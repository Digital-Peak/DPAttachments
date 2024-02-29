<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Acceptance\Install;

use Tests\Support\AcceptanceTester;

class InstallDPAttachmentsCest
{
	public function checkDPAttachmentsDefaults(AcceptanceTester $I): void
	{
		// Disable stats
		$I->updateInDatabase('extensions', ['params' => '{"mode":"3"}'], ['name' => 'plg_system_stats']);

		// Set upload directory
		$I->updateInDatabase('extensions', ['params' => '{"attachment_path":"/tmp/tests"}'], ['name' => 'com_dpattachments']);
	}
}
