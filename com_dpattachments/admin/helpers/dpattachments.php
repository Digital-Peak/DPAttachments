<?php
/**
 * @package		DPAttachments
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsHelper {

    public static $extension = 'com_dpattachments';

    private static $itemCache = array();

    public static function addSubmenu($vName) {
        JHtmlSidebar::addEntry(JText::_('COM_DPATTACHMENTS_ATTACHMENTS'), 'index.php?option=com_dpattachments&view=attachments', $vName == 'attachments');
    }

    public static function canDo($action, $context, $itemId) {
        $key = $context . '.' . $itemId;

        list($component, $modelName) = explode('.', $context);
        if (! key_exists($key, self::$itemCache)) {
            // load the model to get the item

            $tableName = ucfirst($modelName);
            $prefix = ucfirst(str_replace('com_', '', $component)) . 'Table';

            // Handle the content table special
            if ($tableName == 'Article') {
                $prefix = 'JTable';
                $tableName = 'Content';
            }

            // Handle the category table special
            if ($tableName == 'Category') {
                $prefix = 'JTable';
                $tableName = 'Category';
            }

            JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $component . '/tables');
            $table = JTable::getInstance($tableName, $prefix);

            if ($table) {
                $table->load($itemId);
            }

            self::$itemCache[$key] = $table;
        }

        $item = self::$itemCache[$key];
        $user = JFactory::getUser();

        if ($item && $item->id && isset($item->asset_id)) {
            // check permissions with the context
            $autorised = $user->authorise($action, $item->asset_id);
            if ($autorised) {
                return true;
            }

            // if the edit action is requestd we check for edit.own
            if ($action == 'core.edit' && isset($item->created_by)) {
                if ($user->authorise('core.edit.own', $item->asset_id) && $item->created_by == $user->id) {
                    return true;
                }
            }

            // the creator will always have the edit state permissions
            if ($action == 'core.edit.state' && isset($item->created_by)) {
                if ($user->authorise('core.edit.state', $item->asset_id) || $item->created_by == $user->id) {
                    return true;
                }
            }

            return false;
        }

        // no item so we can only check for component permission
        if (! $item || ! $item->id) {
            return $user->authorise($action, $component) || $user->authorise($action, 'com_dpattachments');
        }

        // check permission for the category
        if (isset($item->catid)) {
            $asset = $component . '.category.' . $item->catid;

            if ($user->authorise($action, $asset)) {
                return true;
            }

            // if the edit action is requestd we check for edit.own on the category
            if ($action == 'core.edit' && isset($item->created_by)) {
                if ($user->authorise('core.edit.own', $asset) && $item->created_by == $user->id) {
                    return true;
                }
            }

            // the creator will always have the edit state permissions
            if ($action == 'core.edit.state' && isset($item->created_by)) {
                if ($user->authorise('core.edit.state', $asset) || $item->created_by == $user->id) {
                    return true;
                }
            }
            return false;
        }

        return $user->authorise($action, $component) || $user->authorise($action, 'com_dpattachments');
    }

    public static function getActions() {
        $user = JFactory::getUser();
        $result = new JObject();

        $assetName = 'com_dpattachments';

        $actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete');

        foreach ( $actions as $action ) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    public static function getPath($attachmentPath, $context) {
        $folder = JComponentHelper::getParams('com_dpattachments')->get('attachment_path', 'media/com_dpattachments/attachments/');
        $folder = trim($folder, '/');
        return JPATH_ROOT . '/' . $folder . '/' . $context . '/' . $attachmentPath;
    }

    public static function sendMessage($message, $error = false, array $data = array()) {
        ob_clean();

        if (! $error) {
            JFactory::getApplication()->enqueueMessage($message);
            echo new JResponseJson($data);
        } else {
            JFactory::getApplication()->enqueueMessage($message, 'error');
            echo new JResponseJson($data, '', true);
        }

        JFactory::getApplication()->close();
    }

    public static function size($size) {
        if ($size < 1024) {
            return $filesizebytes . 'B';
        }
        if ($size > 1024) {
            $filekb = $size / 1024;
            if ($filekb < 1024) {
                $flieinkb = round($filekb, 2);
                return $flieinkb . 'Kb';
            }
            if ($filekb > 1024) {
                $filemb = $filekb / 1024;
                $fileinmb = round($filemb, 2);
                return $fileinmb . 'Mb';
            }
        }
    }
}
