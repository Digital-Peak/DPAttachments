<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

class InstallDPAttachmentsCest
{
	public function checkDPAttachmentsDefaults(\AcceptanceTester $I)
	{
		// Remove web dependency
		$I->updateInDatabase('template_styles', ['params' => '{"googleFont":"0"}'], ['template' => 'protostar']);

		// Disable stats
		$I->updateInDatabase('extensions', ['params' => '{"mode":"3"}'], ['name' => 'plg_system_stats']);

		// Set upload directory
		$I->updateInDatabase('extensions', ['params' => '{"attachment_path":"/tmp/tests"}'], ['name' => 'com_dpattachments']);
	}
}
