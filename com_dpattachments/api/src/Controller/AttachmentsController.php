<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Api\Controller;

\defined('_JEXEC') or die;

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use DigitalPeak\Component\DPAttachments\Administrator\Model\AttachmentModel;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Controller\Exception\CheckinCheckout;
use Joomla\CMS\MVC\Controller\Exception\ResourceNotFound;
use Joomla\CMS\MVC\Controller\Exception\Save;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Tobscure\JsonApi\Exception\InvalidParameterException;

class AttachmentsController extends ApiController
{
	protected $contentType = 'attachments';

	protected $default_view = 'attachments';

	public function displayList()
	{
		$filter        = InputFilter::getInstance();
		$apiFilterInfo = $this->input->get('filter', [], 'array');

		if (\array_key_exists('context', $apiFilterInfo)) {
			$this->modelState->set('filter.context', $filter->clean($apiFilterInfo['context'], 'CMD'));
		}

		if (\array_key_exists('item_id', $apiFilterInfo)) {
			$this->modelState->set('filter.item', $filter->clean($apiFilterInfo['item_id'], 'STRING'));
		}

		if (\array_key_exists('search', $apiFilterInfo)) {
			$this->modelState->set('filter.search', $filter->clean($apiFilterInfo['search'], 'STRING'));
		}

		if (\array_key_exists('access', $apiFilterInfo)) {
			$this->modelState->set('filter.access', $filter->clean($apiFilterInfo['access'], 'INT'));
		}

		if (\array_key_exists('author', $apiFilterInfo)) {
			$this->modelState->set('filter.author_id', $filter->clean($apiFilterInfo['author'], 'INT'));
		}

		// Trashed attachments are only returned when explicitly requested, an empty string can't be used
		// as the registry of the model state treats it as not set
		$this->modelState->set(
			'filter.state',
			\array_key_exists('state', $apiFilterInfo) ? $filter->clean($apiFilterInfo['state'], 'INT') : [0, 1, 2]
		);

		$apiListInfo = $this->input->get('list', [], 'array');
		if (\array_key_exists('ordering', $apiListInfo)) {
			$this->modelState->set('list.ordering', $filter->clean($apiListInfo['ordering'], 'STRING'));
		}

		if (\array_key_exists('direction', $apiListInfo)) {
			$this->modelState->set('list.direction', $filter->clean($apiListInfo['direction'], 'STRING'));
		}

		return parent::displayList();
	}

	public function delete($id = null): static
	{
		$attachment = $this->getAttachment((int)($id ?? $this->input->getInt('id')));

		if (!$this->getComponent()->canDo('core.delete', $attachment->context, $attachment->item_id)) {
			throw new NotAllowed(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 403);
		}

		if ((int)$attachment->state !== -2) {
			throw new CheckinCheckout('Resource not in state that can be deleted, must be trashed before it can be deleted', 409);
		}

		$model = $this->getAttachmentModel();
		$ids   = [(int)$attachment->id];
		if (!$model->delete($ids)) {
			throw new \RuntimeException($model->getError() ?: Text::_('JLIB_APPLICATION_ERROR_DELETE'), 500);
		}

		$this->app->setHeader('status', '204');

		return $this;
	}

	protected function allowAdd($data = [])
	{
		if (empty($data['context']) || empty($data['item_id'])) {
			throw new InvalidParameterException('The context and item_id properties are required');
		}

		return $this->getComponent()->canDo('core.edit', (string)$data['context'], (string)$data['item_id']);
	}

	protected function allowEdit($data = [], $key = 'id')
	{
		$attachment = $this->getAttachment((int)($data[$key] ?? 0));

		return $this->getComponent()->canDo('core.edit', $attachment->context, $attachment->item_id);
	}

	protected function save($recordKey = null)
	{
		$data = (array)json_decode($this->input->json->getRaw(), true);

		foreach (FieldsHelper::getFields('com_dpattachments.attachment') as $field) {
			if (!\array_key_exists($field->name, $data)) {
				continue;
			}

			$data['com_fields'] ??= [];

			$data['com_fields'][$field->name] = $data[$field->name];

			unset($data[$field->name]);
		}

		$content = $data['content'] ?? null;
		unset($data['content'], $data['size'], $data['hits']);

		if ($recordKey) {
			// The location of the file can't be changed
			$attachment      = $this->getAttachment((int)$recordKey);
			$data['context'] = $attachment->context;
			$data['item_id'] = $attachment->item_id;
			$data['path']    = $attachment->path;

			// When only the metadata changes, the default save routine can be used
			if ($content === null) {
				$this->input->set('data', $data);

				return parent::save($recordKey);
			}

			$data['title'] ??= $attachment->title;
		}

		if (!\is_string($content) || $content === '') {
			throw new InvalidParameterException('The content property with the base64 encoded file is required');
		}

		$buffer = base64_decode($content, true);
		if ($buffer === false) {
			throw new InvalidParameterException('The content property must be base64 encoded');
		}

		if (empty($data['path'])) {
			throw new InvalidParameterException('The path property with the file name is required');
		}

		$data['path'] = basename((string)$data['path']);
		$data['title'] ??= $data['path'];
		$data['id'] = $recordKey;

		$model     = $this->getAttachmentModel();
		$validData = $this->validate($model, $data);

		$tmpFile = tempnam((string)$this->app->get('tmp_path', sys_get_temp_dir()), 'dpattachments');
		if ($tmpFile === false || file_put_contents($tmpFile, $buffer) === false) {
			throw new Save(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', Text::_('COM_DPATTACHMENTS_UPLOAD_ERROR')));
		}

		try {
			$model->upload($validData, ['name' => $data['path'], 'type' => '', 'tmp_name' => $tmpFile, 'size' => \strlen($buffer)]);
		} catch (\Exception $exception) {
			throw new Save(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $exception->getMessage()), 400, $exception);
		} finally {
			if (is_file($tmpFile)) {
				unlink($tmpFile);
			}
		}

		$recordId = $model->getState($model->getName() . '.id');
		if ($recordId === null) {
			throw new CheckinCheckout(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
		}

		if ($model->checkin($recordId) === false) {
			throw new CheckinCheckout(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
		}

		return $recordId;
	}

	protected function preprocessSaveData(array $data): array
	{
		// Needs to be set because required field in com_fields fallback to jform
		$this->input->set('jform', $data);

		return $data;
	}

	/**
	 * Validates the data against the attachment form.
	 */
	private function validate(AttachmentModel $model, array $data): array
	{
		Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/forms');

		$data      = $this->preprocessSaveData($data);
		$validData = $model->validate($model->getForm($data, false), $data);
		if (\is_array($validData)) {
			return $validData;
		}

		$messages = [];
		foreach (\array_slice($model->getErrors(), 0, 3) as $error) {
			$messages[] = $error instanceof \Exception ? $error->getMessage() : (string)$error;
		}

		throw new InvalidParameterException(implode("\n", $messages));
	}

	/**
	 * Returns the attachment for the given id or throws a not found exception.
	 */
	private function getAttachment(int $id): \stdClass
	{
		$attachment = $id !== 0 ? $this->getAttachmentModel()->getItem($id) : null;
		if (!$attachment instanceof \stdClass || empty($attachment->id)) {
			throw new ResourceNotFound(Text::_('JLIB_APPLICATION_ERROR_RECORD'), 404);
		}

		return $attachment;
	}

	private function getAttachmentModel(): AttachmentModel
	{
		/** @var AttachmentModel $model */
		$model = $this->getModel('Attachment', 'Administrator', ['ignore_request' => true]);

		return $model;
	}

	private function getComponent(): DPAttachmentsComponent
	{
		/** @var DPAttachmentsComponent $component */
		$component = $this->app->bootComponent('dpattachments');

		return $component;
	}
}
