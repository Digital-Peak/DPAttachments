<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2021 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Extension;

use DigitalPeak\Component\DPAttachments\Administrator\Model\AttachmentsModel;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\LegacyComponent;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;
use Joomla\Registry\Registry;

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
	 */
	private array $itemCache = [];

	public function __construct(private readonly CMSApplicationInterface $app, private readonly DatabaseInterface $db, ComponentDispatcherFactoryInterface $dispatcherFactory)
	{
		parent::__construct($dispatcherFactory);
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
	 */
	public function render(string $context, string $itemId, ?Registry $options = null, ?bool $renderForm = true): string
	{
		if (!$this->isEnabled()) {
			return '';
		}

		if (!$options instanceof Registry) {
			$options = new Registry();
		}

		$canEdit     = $this->canDo('core.edit', $context, $itemId);
		$attachments = $this->getAttachments($context, $itemId);

		if ($attachments === [] && !$canEdit) {
			return '';
		}

		PluginHelper::importPlugin('content');

		$event = new Event(
			'onDPAttachmentsBeforeProcessList',
			['context' => $context, 'item_id' => $itemId, 'attachments' => $attachments, 'component' => $this, 'options' => $options]
		);
		$this->app->getDispatcher()->dispatch('onDPAttachmentsBeforeProcessList', $event);

		foreach ($event->getArgument('attachments') as $attachment) {
			$attachment->text = '';
			$this->app->triggerEvent('onContentPrepare', ['com_dpattachments.attachment', &$attachment, &$options, 0]);

			$attachment->event = new \stdClass();
			$results           = $this->app->triggerEvent(
				'onContentAfterTitle',
				['com_dpattachments.attachment', &$attachment, &$options, 0]
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

		$event = new Event(
			'onDPAttachmentsAfterProcessList',
			['context' => $context, 'item_id' => $itemId, 'attachments' => $event->getArgument('attachments'), 'component' => $this, 'options' => $options]
		);
		$this->app->getDispatcher()->dispatch('onDPAttachmentsAfterProcessList', $event);

		$buffer = $this->renderLayout(
			'attachments.render',
			['context' => $context, 'itemid' => $itemId, 'attachments' => $event->getArgument('attachments'), 'options' => $options]
		);

		if (!$canEdit || $renderForm !== true) {
			return $buffer;
		}

		return $buffer . $this->renderLayout('attachment.form', ['itemId' => $itemId, 'context' => $context, 'app' => $this->app]);
	}

	/**
	 * Deletes the attachment for the given context and item ID. Returns true on success, false otherwise.
	 */
	public function delete(string $context, string $itemId): bool
	{
		$ids = [];
		foreach ($this->getAttachments($context, $itemId) as $attachment) {
			unlink($this->getPath($attachment->path, $attachment->context));
			$ids[] = (int)$attachment->id;
		}

		if ($ids === []) {
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
	 */
	public function canDo(string $action, string $context, string $itemId): bool
	{
		$action = $action === 'core.edit' && str_contains($itemId, 'tmp-') ? 'core.create' : $action;

		PluginHelper::importPlugin('dpattachments');
		$event = new Event('onDPAttachmentsCheckPermission', ['action' => $action, 'context' => $context, 'item_id' => $itemId]);
		$this->app->getDispatcher()->dispatch('onDPAttachmentsCheckPermission', $event);
		if ($event->hasArgument('allowed') && $event->getArgument('allowed') === true) {
			return true;
		}

		// Check if there is enough information
		if ($action === '' || $action === '0' || $context === '' || $context === '0' || $itemId === '' || $itemId === '0') {
			return false;
		}

		$key = $context . '.' . $itemId;

		[$component, $modelName] = explode('.', $context);
		if (!\array_key_exists($key, $this->itemCache)) {
			$instance = $this->app->bootComponent($component);

			$table = null;
			if ($instance instanceof LegacyComponent) {
				// Is needed as long as J3 extensions are supported
				// @phpstan-ignore-next-line
				Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $component . '/tables');
				$table = $instance->getMVCFactory()->createTable(ucfirst($modelName), ucfirst(str_replace('com_', '', $component) . 'Table'));
			} elseif ($instance instanceof MVCFactoryServiceInterface) {
				$table = $instance->getMVCFactory()->createTable(ucfirst($modelName), 'Administrator');
			}

			if (!empty($table)) {
				$table->load($itemId);
			}

			$this->itemCache[$key] = $table;
		}

		$user = $this->app->getIdentity();
		if ($user === null) {
			return false;
		}

		$item = $this->itemCache[$key];

		// No item so we can only check for component permission
		if (!$item || (isset($item->id) && !$item->id)) {
			if ($user->authorise($action, $component)) {
				return true;
			}
			return (bool)$user->authorise($action, 'com_dpattachments');
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

		// If the edit action is requested we check for edit.own
		if ($action === 'core.edit' && isset($item->created_by) && $user->authorise('core.edit.own', $asset) && $item->created_by == $user->id) {
			return true;
		}

		// The creator will always have the edit state permissions to trash attachments
		if ($action === 'core.edit.state' && isset($item->created_by) && $item->created_by == $user->id) {
			return true;
		}

		// Fallback to the DPAttachments permissions
		return $user->authorise($action, 'com_dpattachments');
	}

	/**
	 * Returns a local file system path for the given filename and context.
	 */
	public function getPath(string $attachmentPath, string $context): string
	{
		$folder = ComponentHelper::getParams('com_dpattachments')->get('attachment_path', 'media/com_dpattachments/attachments/');
		$folder = trim((string)$folder, '/');

		return JPATH_ROOT . '/' . $folder . '/' . $context . '/' . $attachmentPath;
	}

	/**
	 * Helper function to create a human readable size string for the given size which is in bytes.
	 */
	public function size(int $size): string
	{
		// Size in bytes
		if ($size <= 1024) {
			return $size . $this->app->getLanguage()->_('COM_DPATTACHMENTS_BYTE_SHORT');
		}

		// Size in kilo bytes
		$filekb = $size / 1024;
		if ($filekb <= 1024) {
			$flieinkb = round($filekb, 2);

			return $flieinkb . $this->app->getLanguage()->_('COM_DPATTACHMENTS_KILOBYTE_SHORT');
		}

		// Size in mega bytes
		$filemb   = $filekb / 1024;
		$fileinmb = round($filemb, 2);

		return $fileinmb . $this->app->getLanguage()->_('COM_DPATTACHMENTS_MEGA_BYTE_SHORT');
	}

	/**
	 * Shortcut function to render layouts from dpattachments.
	 */
	public function renderLayout(string $name, array $data): string
	{
		$event = new Event(
			'onDPAttachmentsBeforeRenderLayout',
			['name' => $name, 'data' => $data, 'component' => $this]
		);
		$this->app->getDispatcher()->dispatch('onDPAttachmentsBeforeRenderLayout', $event);

		$content = LayoutHelper::render(
			$name,
			$data,
			'',
			['component' => 'com_dpattachments', 'client' => 0]
		);

		$event = new Event(
			'onDPAttachmentsAfterRenderLayout',
			['name' => $name, 'data' => $data, 'content' => $content, 'component' => $this]
		);
		$this->app->getDispatcher()->dispatch('onDPAttachmentsAfterRenderLayout', $event);

		return $event->getArgument('content');
	}

	public function validateSection($section, $item = null): ?string
	{
		if ($this->app->isClient('site') && $section === 'form') {
			$section = 'attachment';
		}

		if ($section != 'attachment') {
			return null;
		}

		return $section;
	}

	public function getContexts(): array
	{
		$this->app->getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR);

		return [
			'com_dpattachments.attachment' => $this->app->getLanguage()->_('COM_DPATTACHMENTS')
		];
	}

	/**
	 * Internal helper function to check if the actual menu item or component is enabled for attachment support.
	 */
	private function isEnabled(): bool
	{
		$input  = $this->app->getInput();
		$params = ComponentHelper::getParams('com_dpattachments');

		// Check for menu items to include
		$menuItems = $params->get('menuitems');
		if (!empty($menuItems)) {
			if (!\is_array($menuItems)) {
				$menuItems = [$menuItems];
			}

			if (!\in_array($input->getInt('Itemid', 0), $menuItems)) {
				return false;
			}
		}

		$menuItems = $params->get('menuitems_exclude');
		if (!empty($menuItems)) {
			if (!\is_array($menuItems)) {
				$menuItems = [$menuItems];
			}

			if (\in_array($input->getInt('Itemid', 0), $menuItems)) {
				return false;
			}
		}

		// Check for components to include
		$components = $params->get('components');
		if (!empty($components)) {
			if (!\is_array($components)) {
				$components = [$components];
			}

			if (!\in_array($input->getCmd('option'), $components)) {
				return false;
			}
		}

		$components = $params->get(
			'components_exclude',
			['com_cache', 'com_actionlogs', 'com_menus', 'com_config', 'com_scheduler', 'com_plugins', 'com_media']
		);
		if (!empty($components)) {
			if (!\is_array($components)) {
				$components = [$components];
			}

			if (\in_array($input->getCmd('option'), $components)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Internal helper function to get the attachments for the given context and item ID.
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
