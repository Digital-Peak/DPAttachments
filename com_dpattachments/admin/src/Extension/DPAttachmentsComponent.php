<?php
/**
 * @package    DPCases
 * @copyright  Copyright (C) 2021 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Extension;

use DigitalPeak\Component\DPAttachments\Administrator\Model\AttachmentsModel;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\LegacyComponent;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use stdClass;

/**
 * Public DPAttachments component.
 *
 * This class can be used to enable attachment support in your Joomla extension.
 *
 * To include attachment support use the following code in your view (default.php) file:
 *
 * echo $app->bootComponent('dpattachments')->render('com_foo.bar', $item->id);
 */
class DPAttachmentsComponent extends MVCComponent implements FieldsServiceInterface
{
	/**
	 * The cached items.
	 *
	 * @var array
	 */
	private $itemCache = [];

	/**
	 * @var DatabaseInterface $db
	 */
	private $db;

	/**
	 * @var CMSApplicationInterface $app
	 */
	private $app;

	public function __construct(CMSApplicationInterface $app, DatabaseInterface $db, ComponentDispatcherFactoryInterface $dispatcherFactory)
	{
		parent::__construct($dispatcherFactory);

		$this->app = $app;
		$this->db  = $db;
	}

	/**
	 * The render function which takes care to render the HTML code. A HTML string is
	 * returned which can be printed in any Joomla view.
	 *
	 * The options array or Registry option can customize the following attributes:
	 * - render.columns: The amount of columns to render
	 *
	 * The render form parameter defines if the upload form should be rendered when the edit
	 * permission exists.
	 *
	 * @param string $context
	 * @param string $itemId
	 * @param mixed  $options
	 * @param bool   $renderForm
	 *
	 * @return string
	 */
	public function render(string $context, string $itemId, $options = null, $renderForm = true): string
	{
		if (!$this->isEnabled()) {
			return '';
		}

		if (empty($options)) {
			$options = new Registry();
		}

		if (is_array($options)) {
			$options = new Registry($options);
		}

		$canEdit     = $this->canDo('core.edit', $context, $itemId);
		$attachments = $this->getAttachments($context, $itemId);

		if (!$attachments && !$canEdit) {
			return '';
		}

		$user = $this->app->getIdentity();
		PluginHelper::importPlugin('content');
		foreach ($attachments as $key => $attachment) {
			if ($attachment->context === 'com_dpcalendar.event'
				&& !$this->canDo('core.admin', 'com_dpcalendar.event', $itemId)
				&& $attachment->params->get('dpcalendar_event_ticket')
				&& $options->get('item')) {
				if (empty($options->get('item')->tickets)) {
					unset($attachments[$key]);
					continue;
				}

				$found = false;
				foreach ($options->get('item')->tickets as $ticket) {
					if ($user->id && ($ticket->email === $user->email || $ticket->user_id == $user->id)) {
						$found = true;
					}
				}

				if (!$found) {
					unset($attachments[$key]);
					continue;
				}
			}

			$attachment->text = '';
			$this->app->triggerEvent('onContentPrepare', ['com_dpattachments.attachment', &$attachment, &$options, 0]);

			$attachment->event = new stdClass();
			$results           = $this->app->triggerEvent(
				'onContentAfterTitle',
				['com_dpattachments.attachment', &$attachment, &$this->params, 0]
			);
			$attachment->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $this->app->triggerEvent(
				'onContentBeforeDisplay',
				['com_dpattachments.attachment', &$attachment, &$options, 0]
			);
			$attachment->event->beforeDisplayAttachment = trim(implode("\n", $results));

			$results = $this->app->triggerEvent(
				'onContentAfterDisplay',
				['com_dpattachments.attachment', &$attachment, &$options, 0]
			);
			$attachment->event->afterDisplayAttachment = trim(implode("\n", $results));
		}

		$buffer = $this->renderLayout(
			'attachments.render',
			['context' => $context, 'itemid' => $itemId, 'attachments' => $attachments, 'options' => $options]
		);

		if (!$canEdit || !$renderForm) {
			return $buffer;
		}

		$buffer .= $this->renderLayout('attachment.form', ['itemId' => $itemId, 'context' => $context]);

		return $buffer;
	}

	/**
	 * Deletes the attachment for the given context and item ID. Returns true on success, false otherwise.
	 *
	 * @param string $context
	 * @param string $itemId
	 *
	 * @return bool
	 */
	public function delete(string $context, string $itemId): bool
	{
		$ids = [];
		foreach ($this->getAttachments($context, $itemId) as $attachment) {
			unlink($this->getPath($attachment->path, $attachment->context));
			$ids[] = (int)$attachment->id;
		}

		if (empty($ids)) {
			return true;
		}

		$query = $this->db->getQuery(true);
		$query->delete('#__dpattachments');
		$query->where('id in (' . implode(',', $ids) . ')');
		$this->db->setQuery($query)->execute();

		return true;
	}

	/**
	 * Check if the given action can be performed for the item in the given context.
	 * The context will be splitted and a table is tried to be loaded for the given context with the data of the item ID.
	 * For example for the context com_dpcalendar.event a table with the name DPCalendarTableEvent will be loaded. On a second step
	 * the loaded table instance will be checked if it has an asset_id or catid field to check permissions against them. If this is not
	 * the case a fallback will be done to the DPAttachments options permission configuration.
	 *
	 * @param string $action
	 * @param string $context
	 * @param string $itemId
	 *
	 * @return bool
	 */
	public function canDo(string $action, string $context, string $itemId): bool
	{
		$key = $context . '.' . $itemId;

		list($component, $modelName) = explode('.', $context);
		if (!key_exists($key, $this->itemCache)) {
			$instance = $this->app->bootComponent($component);

			$table = null;
			if ($instance instanceof LegacyComponent) {
				Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $component . '/tables');
				$table = $instance->getMVCFactory()->createTable(ucfirst($modelName), ucfirst(str_replace('com_', '', $component) . 'Table'));
			} elseif ($instance instanceof MVCFactoryServiceInterface) {
				$table = $instance->getMVCFactory()->createTable(ucfirst($modelName), 'Administrator');
			}

			if ($table) {
				$table->load($itemId);
			}

			$this->itemCache[$key] = $table;
		}

		$user = $this->app->getIdentity();

		$item = $this->itemCache[$key];

		// No item so we can only check for component permission
		if (!$item || (isset($item->id) && !$item->id)) {
			return $user->authorise($action, $component) || $user->authorise($action, 'com_dpattachments');
		}

		$asset = $component;
		if (isset($item->asset_id)) {
			$asset = $item->asset_id;
		} elseif (isset($item->catid)) {
			$asset = $component . '.category.' . $item->catid;
		}

		// Check direct permission
		if ($user->authorise($action, $asset)) {
			return true;
		}

		// If the edit action is requestd we check for edit.own
		if ($action == 'core.edit' && isset($item->created_by) && $user->authorise('core.edit.own', $asset) && $item->created_by == $user->id) {
			return true;
		}

		// The creator will always have the edit state permissions to trash attachments
		if ($action == 'core.edit.state' && isset($item->created_by) && $item->created_by == $user->id) {
			return true;
		}

		// Fallback to the DPAttachments permissions
		return $user->authorise($action, 'com_dpattachments');
	}

	/**
	 * Returns a local file system path for the given filename and context.
	 *
	 * @param string $attachmentPath
	 * @param string $context
	 *
	 * @return string
	 */
	public function getPath(string $attachmentPath, string $context): string
	{
		$folder = ComponentHelper::getParams('com_dpattachments')->get('attachment_path', 'media/com_dpattachments/attachments/');
		$folder = trim($folder, '/');

		return JPATH_ROOT . '/' . $folder . '/' . $context . '/' . $attachmentPath;
	}

	/**
	 * Helper function to create a human readable size string for the given size which is in bytes.
	 *
	 * @param integer $size
	 *
	 * @return string
	 */
	public function size(int $size): string
	{
		// Size in bytes
		if ($size <= 1024) {
			return $size . Text::_('COM_DPATTACHMENTS_BYTE_SHORT');
		}

		// Size in kilo bytes
		$filekb = $size / 1024;
		if ($filekb <= 1024) {
			$flieinkb = round($filekb, 2);

			return $flieinkb . Text::_('COM_DPATTACHMENTS_KILOBYTE_SHORT');
		}

		// Size in mega bytes
		$filemb   = $filekb / 1024;
		$fileinmb = round($filemb, 2);

		return $fileinmb . Text::_('COM_DPATTACHMENTS_MEGA_BYTE_SHORT');
	}

	/**
	 * Shortcut function to render layouts from dpattachments.
	 *
	 * @param string $name
	 * @param array $data
	 *
	 * @return string
	 */
	public function renderLayout(string $name, array $data): string
	{
		return LayoutHelper::render(
			$name,
			$data,
			null,
			['component' => 'com_dpattachments', 'client' => 0]
		);
	}

	public function validateSection($section, $item = null)
	{
		if (Factory::getApplication()->isClient('site')) {
			switch ($section) {
				case 'form':
					$section = 'attachment';
			}
		}

		if ($section != 'attachment') {
			return null;
		}

		return $section;
	}

	public function getContexts(): array
	{
		Factory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR);

		$contexts = [
			'com_dpattachments.attachment' => Text::_('COM_DPATTACHMENTS')
		];

		return $contexts;
	}

	/**
	 * Internal helper function to check if the actual menu item or component is enabled for attachment support.
	 *
	 * @return bool
	 */
	private function isEnabled(): bool
	{
		$input  = $this->app->getInput();
		$params = ComponentHelper::getParams('com_dpattachments');

		// Check for menu items to include
		$menuItems = $params->get('menuitems');
		if (!empty($menuItems)) {
			if (!is_array($menuItems)) {
				$menuItems = [$menuItems];
			}

			if (!in_array($input->getInt('Itemid'), $menuItems)) {
				return false;
			}
		}

		$menuItems = $params->get('menuitems_exclude');
		if (!empty($menuItems)) {
			if (!is_array($menuItems)) {
				$menuItems = [$menuItems];
			}

			if (in_array($input->getInt('Itemid'), $menuItems)) {
				return false;
			}
		}

		// Check for components to include
		$components = $params->get('components');
		if (!empty($components)) {
			if (!is_array($components)) {
				$components = [$components];
			}

			if (!in_array($input->getCmd('option'), $components)) {
				return false;
			}
		}

		$components = $params->get('components_exclude');
		if (!empty($components)) {
			if (!is_array($components)) {
				$components = [$components];
			}

			if (in_array($input->getCmd('option'), $components)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Internal helper function to get the attachments for the given context and item ID.
	 *
	 * @param string $context
	 * @param string $itemId
	 *
	 * @return array
	 */
	private function getAttachments(string $context, string $itemId): array
	{
		/** @var AttachmentsModel $model */
		$model = $this->getMVCFactory()->createModel('Attachments', 'Administrator', ['ignore_request' => true]);
		$model->getState();
		$model->setState('filter.item', $itemId);
		$model->setState('filter.context', $context);
		$model->setState('filter.state', 1);
		$model->setState('list.limit', 1000);
		$model->setState('list.start', 0);

		return $model->getItems();
	}
}
