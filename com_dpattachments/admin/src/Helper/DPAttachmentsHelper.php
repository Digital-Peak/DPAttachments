<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;

class DPAttachmentsHelper
{
	public static $extension = 'com_dpattachments';

	public static function getActions(): CMSObject
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
}
