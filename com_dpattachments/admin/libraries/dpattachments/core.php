<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2015 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.filesystem.file');

/**
 * Public DPAttachments API class.
 *
 * This class can be used to enable attachment support in your
 * Joomla extension.
 *
 * To include attachment support use the following code in your
 * view (default.php) file:
 *
 * JLoader::import('components.com_dpattachments.libraries.dpattachments.core',
 * JPATH_ADMINISTRATOR);
 * if (class_exists('DPAttachmentsCore')) {
 * echo DPAttachmentsCore::render('com_foo.bar', $item->id);
 * }
 */
class DPAttachmentsCore
{

	/**
	 * The cached items.
	 *
	 * @var array
	 */
	private static $itemCache = array();

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
	 * @param mixed $options
	 * @return string
	 */
	public static function render($context, $itemId, $options = null)
	{
		if (!self::isEnabled())
		{
			return '';
		}

		if (empty($options))
		{
			$options = new JRegistry();
		}

		if (is_array($options))
		{
			$options = new JRegistry($options);
		}

		$canEdit = self::canDo('core.edit', $context, $itemId);
		$attachments = self::getAttachments($context, $itemId);

		if (!$attachments && !$canEdit)
		{
			return '';
		}

		$buffer = JLayoutHelper::render('attachments.render', array(
				'attachments' => $attachments,
				'options' => $options
		), null, array(
				'component' => 'com_dpattachments',
				'client' => 0
		));

		if (!$canEdit)
		{
			return $buffer;
		}

		$buffer .= JLayoutHelper::render('attachment.form', array(
				'itemId' => $itemId,
				'context' => $context
		), null, array(
				'component' => 'com_dpattachments',
				'client' => 0
		));

		return $buffer;
	}

	/**
	 * Deletes the attachment for the given context and
	 * item ID.
	 * Returns true on success, false otherwise.
	 *
	 * @param string $context
	 * @param string $itemId
	 * @return boolean
	 */
	public static function delete($context, $itemId)
	{
		$ids = array();
		foreach (self::getAttachments($context, $itemId) as $attachment)
		{
			JFile::delete(self::getPath($attachment->path, $attachment->context));
			$ids[] = (int)$attachment->id;
		}

		if (empty($ids))
		{
			return true;
		}

		$db = JFactory::getDbo();
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
	 * @return boolean
	 */
	public static function canDo($action, $context, $itemId)
	{
		$key = $context . '.' . $itemId;

		list ($component, $modelName) = explode('.', $context);
		if (!key_exists($key, self::$itemCache))
		{
			// Load the model to get the item
			$tableName = ucfirst($modelName);
			$prefix = ucfirst(str_replace('com_', '', $component)) . 'Table';

			// Handle the content table special
			if ($tableName == 'Article')
			{
				$prefix = 'JTable';
				$tableName = 'Content';
			}

			// Handle the category table special
			if ($tableName == 'Category')
			{
				$prefix = 'JTable';
				$tableName = 'Category';
			}

			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $component . '/tables');
			$table = false;
			if (JFile::exists(JPATH_ADMINISTRATOR . '/components/' . $component . '/tables/' . strtolower($tableName) . '.php'))
			{
				$table = JTable::getInstance($tableName, $prefix);
			}

			if ($table)
			{
				$table->load($itemId);
			}

			self::$itemCache[$key] = $table;
		}

		$user = JFactory::getUser();

		$item = self::$itemCache[$key];

		// No item so we can only check for component permission
		if (!$item || (isset($item->id) && !$item->id))
		{
			return $user->authorise($action, $component) || $user->authorise($action, 'com_dpattachments');
		}

		$asset = $component;
		if (isset($item->asset_id))
		{
			$asset = $item->asset_id;
		}
		else if (isset($item->catid))
		{
			$asset = $component . '.category.' . $item->catid;
		}

		// Check direct permission
		if ($user->authorise($action, $asset))
		{
			return true;
		}

		// If the edit action is requestd we check for edit.own
		if ($action == 'core.edit' && isset($item->created_by))
		{
			if ($user->authorise('core.edit.own', $asset) && $item->created_by == $user->id)
			{
				return true;
			}
		}

		// The creator will always have the edit state permissions to trsah
		// attachments
		if ($action == 'core.edit.state' && isset($item->created_by))
		{
			if ($item->created_by == $user->id)
			{
				return true;
			}
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
	 * @return string
	 */
	public static function getPath($attachmentPath, $context)
	{
		$folder = JComponentHelper::getParams('com_dpattachments')->get('attachment_path', 'media/com_dpattachments/attachments/');
		$folder = trim($folder, '/');
		return JPATH_ROOT . '/' . $folder . '/' . $context . '/' . $attachmentPath;
	}

	/**
	 * Helper function to create a human readable
	 * size string for the given size which is in bytes.
	 *
	 * @param integer $size
	 * @return string
	 */
	public static function size($size)
	{
		// Size in bytes
		if ($size <= 1024)
		{
			return $size . JText::_('COM_DPATTACHMENTS_BYTE_SHORT');
		}

		// Size in kilo bytes
		$filekb = $size / 1024;
		if ($filekb <= 1024)
		{
			$flieinkb = round($filekb, 2);
			return $flieinkb . JText::_('COM_DPATTACHMENTS_KILOBYTE_SHORT');
		}

		// Size in mega bytes
		$filemb = $filekb / 1024;
		$fileinmb = round($filemb, 2);
		return $fileinmb . JText::_('COM_DPATTACHMENTS_MEGA_BYTE_SHORT');
	}

	/**
	 * Internal helper function to check if the acual menu item or component
	 * is enabled for attachment support.
	 *
	 * @return boolean
	 */
	private static function isEnabled()
	{
		$input = JFactory::getApplication()->input;
		$params = JComponentHelper::getParams('com_dpattachments');

		// Check for menu items to include
		$menuItems = $params->get('menuitems');
		if (!empty($menuItems))
		{
			if (!is_array($menuItems))
			{
				$menuItems = array(
						$menuItems
				);
			}

			if (!in_array($input->getInt('Itemid'), $menuItems))
			{
				return false;
			}
		}

		$menuItems = $params->get('menuitems_exclude');
		if (!empty($menuItems))
		{
			if (!is_array($menuItems))
			{
				$menuItems = array(
						$menuItems
				);
			}

			if (in_array($input->getInt('Itemid'), $menuItems))
			{
				return false;
			}
		}

		// Check for components to include
		$components = $params->get('components');
		if (!empty($components))
		{
			if (!is_array($components))
			{
				$components = array(
						$components
				);
			}

			if (!in_array($input->getCmd('option'), $components))
			{
				return false;
			}
		}

		$components = $params->get('components_exclude');
		if (!empty($components))
		{
			if (!is_array($components))
			{
				$components = array(
						$components
				);
			}

			if (in_array($input->getCmd('option'), $components))
			{
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
	 * @return array
	 */
	private static function getAttachments($context, $itemId)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/models', 'DPAttachmentsModel');
		$model = JModelLegacy::getInstance('Attachments', 'DPAttachmentsModel');
		$model->getState();
		$model->setState('filter.item', $itemId);
		$model->setState('filter.context', $context);
		$model->setState('list.limit', 1000);
		$model->setState('list.start', 0);

		return $model->getItems();
	}
}
