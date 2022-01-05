<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Helper;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Response\JsonResponse;

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

	public static function getActions()
	{
		$user   = Factory::getUser();
		$result = new CMSObject();

		$assetName = 'com_dpattachments';

		$actions = [
			'core.admin',
			'core.manage',
			'core.create',
			'core.edit',
			'core.edit.own',
			'core.edit.state',
			'core.delete'
		];

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	public static function sendMessage($message, $error = false, array $data = [])
	{
		ob_clean();

		if (!$error) {
			Factory::getApplication()->enqueueMessage($message);
			echo new JsonResponse($data);
		} else {
			Factory::getApplication()->enqueueMessage($message, 'error');
			echo new JsonResponse($data, '', true);
		}

		Factory::getApplication()->close();
	}
}
