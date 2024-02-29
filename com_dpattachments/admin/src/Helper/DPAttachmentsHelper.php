<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Component\DPAttachments\Administrator\Helper;

use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

class DPAttachmentsHelper
{
	public static string $extension = 'com_dpattachments';

	public static function getActions(User $user): Registry
	{
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

		$result = new Registry();
		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}
