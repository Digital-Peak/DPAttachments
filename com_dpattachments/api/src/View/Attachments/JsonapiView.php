<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Api\View\Attachments;

\defined('_JEXEC') or die;

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use DigitalPeak\Component\DPAttachments\Administrator\Model\AttachmentModel;
use DigitalPeak\Component\DPAttachments\Api\Serializer\DPAttachmentsSerializer;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Registry\Registry;

class JsonapiView extends BaseApiView
{
	/** @var DPAttachmentsSerializer */
	public $serializer;

	protected $fieldsToRenderItem = [
		'id',
		'context',
		'item_id',
		'path',
		'title',
		'size',
		'description',
		'state',
		'access',
		'publish_up',
		'publish_down',
		'created',
		'created_by',
		'created_by_alias',
		'modified',
		'modified_by',
		'hits',
		'version',
		'download_url'
	];

	protected $fieldsToRenderList = [
		'id',
		'context',
		'item_id',
		'path',
		'title',
		'size',
		'description',
		'state',
		'access',
		'publish_up',
		'publish_down',
		'created',
		'created_by',
		'created_by_alias',
		'modified',
		'modified_by',
		'hits',
		'version',
		'download_url'
	];

	protected $relationship = ['created_by', 'modified_by'];

	public function __construct($config = [])
	{
		if (\array_key_exists('contentType', $config)) {
			$this->serializer = new DPAttachmentsSerializer($config['contentType']);
		}

		parent::__construct($config);
	}

	public function displayList(?array $items = null)
	{
		foreach (FieldsHelper::getFields('com_dpattachments.attachment') as $field) {
			$this->fieldsToRenderList[] = $field->name;
		}

		if ($items === null) {
			$component = $this->getComponent();

			// Only return the attachments the user is allowed to see
			$items = array_values(array_filter(
				$this->getModel()->getItems() ?: [],
				static fn (\stdClass $attachment): bool => $component->canView($attachment)
			));
			$items = array_map($this->prepareItem(...), $items);
		}

		return parent::displayList($items);
	}

	public function displayItem($item = null)
	{
		foreach (FieldsHelper::getFields('com_dpattachments.attachment') as $field) {
			$this->fieldsToRenderItem[] = $field->name;
		}

		if ($item === null) {
			/** @var AttachmentModel $model */
			$model = $this->getModel();
			$item  = $model->getItem();

			if (!$item instanceof \stdClass || empty($item->id) || !$this->getComponent()->canView($item)) {
				throw new RouteNotFoundException('Item does not exist');
			}

			$item = $this->prepareItem($item);
		}

		return parent::displayItem($item);
	}

	/**
	 * @param \stdClass $item
	 */
	protected function prepareItem($item)
	{
		PluginHelper::importPlugin('content', null, true, $this->getDispatcher());

		$item->text = $item->description;
		$params     = $item->params ?? null;
		$this->getDispatcher()->dispatch(
			'onContentPrepare',
			new ContentPrepareEvent('onContentPrepare', [
				'context' => 'com_dpattachments.attachment',
				'subject' => $item,
				'params'  => $params instanceof Registry ? $params : new Registry($params),
				'page'    => 0
			])
		);
		$item->description = $item->text;

		foreach (FieldsHelper::getFields('com_dpattachments.attachment', $item, true) as $field) {
			$item->{$field->name} = $field->apivalue ?? $field->rawvalue;
		}

		$item->download_url = Uri::root() . 'index.php?option=com_dpattachments&task=attachment.download&id=' . (int)$item->id;

		return parent::prepareItem($item);
	}

	private function getComponent(): DPAttachmentsComponent
	{
		/** @var DPAttachmentsComponent $component */
		$component = Factory::getApplication()->bootComponent('dpattachments');

		return $component;
	}
}
