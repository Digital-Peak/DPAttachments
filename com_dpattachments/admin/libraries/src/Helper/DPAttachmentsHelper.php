<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2020 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DPAttachments\Helper;

defined('_JEXEC') or die();

class DPAttachmentsHelper
{
	public static $extension = 'com_dpattachments';

	public static function renderContext($context)
	{
		$context = str_replace('com_', '', strtolower($context));

		$buffer = '';
		foreach (explode('.', $context) as $part) {
			$buffer .= ucfirst($part) . ' ';
		}

		return trim($buffer, ' ');
	}

	public static function addSubmenu($vName)
	{
		\JHtmlSidebar::addEntry(
			\JText::_('COM_DPATTACHMENTS_ATTACHMENTS'),
			'index.php?option=com_dpattachments&view=attachments',
			$vName == 'attachments'
		);
	}

	public static function getActions()
	{
		$user   = \JFactory::getUser();
		$result = new \JObject();

		$assetName = 'com_dpattachments';

		$actions = array(
			'core.admin',
			'core.manage',
			'core.create',
			'core.edit',
			'core.edit.own',
			'core.edit.state',
			'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function sendMessage($message, $error = false, array $data = array())
	{
		ob_clean();

		if (!$error) {
			\JFactory::getApplication()->enqueueMessage($message);
			echo new \JResponseJson($data);
		} else {
			\JFactory::getApplication()->enqueueMessage($message, 'error');
			echo new \JResponseJson($data, '', true);
		}

		\JFactory::getApplication()->close();
	}
}
