<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Helper;

defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * Public DPAttachments API class.
 *
 * This class can be used to enable attachment support in your
 * Joomla extension.
 *
 * To include attachment support use the following code in your
 * view (default.php) file:
 *
 * if (class_exists('\DigitalPeak\Component\DPAttachments\Administrator\Helper\Core')) {
 *     echo DigitalPeak\Component\DPAttachments\Administrator\Helper\Core::render('com_foo.bar', $item->id);
 * }
 */
class Core
{
	/**
	 * The cached items.
	 *
	 * @var array
	 */
	private static $itemCache = [];

	/**
	 * The render function which takes care to render the HTML
	 * code.
	 * A HTML string is returned which can be printed in any
	 * Joomla view.
	 * The options array or JRegistry option can customize the following
	 * attributes:
	 * - render.columns: The amount of columns to render
	 *
	 * @param string $context
	 * @param string $itemId
	 * @param mixed  $options
	 *
	 * @return string
	 */
	public static function render($context, $itemId, $options = null)
	{
		if (!self::isEnabled()) {
			return '';
		}

		if (empty($options)) {
			$options = new Registry();
		}

		if (is_array($options)) {
			$options = new Registry($options);
		}

		$canEdit     = self::canDo('core.edit', $context, $itemId);
		$attachments = self::getAttachments($context, $itemId);

		if (!$attachments && !$canEdit) {
			return '';
		}

		$buffer = self::renderLayout(
			'attachments.render',
			['context' => $context, 'itemid' => $itemId, 'attachments' => $attachments, 'options' => $options]
		);

		if (!$canEdit) {
			return $buffer;
		}

		$buffer .= self::renderLayout('attachment.form', ['itemId' => $itemId, 'context' => $context]);

		return $buffer;
	}

	/**
	 * Deletes the attachment for the given context and
	 * item ID.
	 * Returns true on success, false otherwise.
	 *
	 * @param string $context
	 * @param string $itemId
	 *
	 * @return boolean
	 */
	public static function delete($context, $itemId)
	{
		$ids = [];
		foreach (self::getAttachments($context, $itemId) as $attachment) {
			\JFile::delete(self::getPath($attachment->path, $attachment->context));
			$ids[] = (int)$attachment->id;
		}

		if (empty($ids)) {
			return true;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__dpattachments');
		$query->where('id in (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		$db->execute();

		return true;
	}

	/**
	 * Check if the given action can be performed for the item in
	 * the given context.
	 * The context will be splitted and a table is tried to be loaded
	 * for the given context with the data of the item ID.
	 * For example for the context com_dpcalendar.event a table with
	 * the name DPCalendarTableEvent will be loaded. On a second step
	 * the loaded table instance will be checked if it has an asset_id
	 * or catid field to check permissions against them. If this is not
	 * the case a fallback will be done to the DPAttachments options permission
	 * configuration.
	 *
	 * @param string $action
	 * @param string $context
	 * @param string $itemId
	 *
	 * @return boolean
	 */
	public static function canDo($action, $context, $itemId)
	{
		$key = $context . '.' . $itemId;

		list($component, $modelName) = explode('.', $context);
		if (!key_exists($key, self::$itemCache)) {
			// Load the model to get the item
			$tableName = ucfirst($modelName);
			$prefix    = ucfirst(str_replace('com_', '', $component)) . 'Table';

			// Handle the content table special
			if ($tableName == 'Article') {
				$prefix    = 'JTable';
				$tableName = 'Content';
			}

			// Handle the category table special
			if ($tableName == 'Category') {
				$prefix    = 'JTable';
				$tableName = 'Category';
			}

			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $component . '/tables');
			$table = Table::getInstance($tableName, $prefix);

			if (!$table && version_compare(4, JVERSION, '<=')) {
				$instance = Factory::getApplication()->bootComponent($component);
				if ($instance instanceof MVCComponent) {
					$table = $instance->getMVCFactory()->createTable($tableName, 'Administrator');
				}
			}

			if ($table) {
				$table->load($itemId);
			}

			self::$itemCache[$key] = $table;
		}

		$user = Factory::getUser();

		$item = self::$itemCache[$key];

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
	 * Returns a local file system pathe for the given filename and
	 * context.
	 *
	 * @param string $attachmentPath
	 * @param string $context
	 *
	 * @return string
	 */
	public static function getPath($attachmentPath, $context)
	{
		$folder = ComponentHelper::getParams('com_dpattachments')->get('attachment_path', 'media/com_dpattachments/attachments/');
		$folder = trim($folder, '/');

		return JPATH_ROOT . '/' . $folder . '/' . $context . '/' . $attachmentPath;
	}

	/**
	 * Helper function to create a human readable
	 * size string for the given size which is in bytes.
	 *
	 * @param integer $size
	 *
	 * @return string
	 */
	public static function size($size)
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
	 * @param $name
	 * @param $data
	 *
	 * @return string
	 */
	public static function renderLayout($name, $data)
	{
		return LayoutHelper::render(
			$name,
			$data,
			null,
			['component' => 'com_dpattachments', 'client' => 0]
		);
	}

	/**
	 * Internal helper function to check if the acual menu item or component
	 * is enabled for attachment support.
	 *
	 * @return boolean
	 */
	private static function isEnabled()
	{
		$input  = Factory::getApplication()->input;
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
	 * Internal helper function to get the attachments for the given
	 * context and item ID.
	 *
	 * @param string $context
	 * @param string $itemId
	 *
	 * @return array
	 */
	private static function getAttachments($context, $itemId)
	{
		BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/models', 'DPAttachmentsModel');
		$model = BaseDatabaseModel::getInstance('Attachments', 'DPAttachmentsModel', ['ignore_request' => true]);
		$model->getState();
		$model->setState('filter.item', $itemId);
		$model->setState('filter.context', $context);
		$model->setState('filter.state', 1);
		$model->setState('list.limit', 1000);
		$model->setState('list.start', 0);

		return $model->getItems();
	}
}
