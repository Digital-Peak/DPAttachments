<?php
/**
 * @package    DPCalendar
 * @copyright  Copyright (C) 2021 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Acceptance\Administrator;

use Tests\Support\BasicDPAttachmentsCestClass;
use Tests\Support\Page\Acceptance\Administrator\AttachmentFormPage;
use Tests\Support\Page\Acceptance\Administrator\AttachmentsListPage;
use Tests\Support\Step\Acceptance\Attachment;

class AttachmentEditCest extends BasicDPAttachmentsCestClass
{
	public function canEditAttachment(Attachment $I)
	{
		$I->wantToTest('that it is possible to edit an attachment.');

		$I->createAttachment(['path' => 'test.jpg']);

		$I->doAdministratorLogin(null, null, false);
		$I->amOnPage(AttachmentsListPage::$url);
		$I->click('test.jpg');
		$I->waitForElement(AttachmentFormPage::$rootClass);

		$I->fillField('#jform_title', 'New Test File');
		$I->click('Save & Close');
		$I->waitForText('Item saved');

		$I->see('New Test File', 'a');
	}
}
