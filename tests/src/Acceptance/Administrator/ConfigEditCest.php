<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2022 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Acceptance\Administrator;

use Tests\Support\AcceptanceTester;
use Tests\Support\BasicDPAttachmentsCestClass;
use Tests\Support\Step\Attachment;

class ConfigEditCest extends BasicDPAttachmentsCestClass
{
	public function _before(AcceptanceTester $I): void
	{
		parent::_before($I);

		$I->enablePlugin('plg_content_dpattachments');
	}

	public function cantSeeAttachmentDetails(Attachment $I): void
	{
		$I->wantToTest('that the attachment details are not shown in the global config form.');

		$I->doAdministratorLogin();
		$I->amOnPage('/administrator/index.php?option=com_config&view=component&component=com_content');

		$I->dontSee('Attachments', '#config');
	}
}
