<?php
/**
 * @package    DPCalendar
 * @copyright  Copyright (C) 2019 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

use Page\Acceptance\Administrator\AttachmentsListPage;
use Step\Acceptance\Attachment;

class AttachmentsListCest extends \BasicDPAttachmentsCestClass
{
	public function _before(\AcceptanceTester $I)
	{
		parent::_before($I);

		$I->doAdministratorLogin(null, null, false);
	}

	public function canNavigateToAttachmentForm(Attachment $I)
	{
		$I->wantToTest('that it is possible to edit an attachment.');

		$I->createAttachment(['path' => 'test.jpg']);

		$I->amOnPage(AttachmentsListPage::$url);
		$I->waitForElement(AttachmentsListPage::$rootClass);
		$I->click('test.jpg');
		$I->waitForElement('.com-dpattachments-attachment-form');

		$I->seeInCurrentUrl('view=attachment');
	}

	public function seeAttachmentsInList(Attachment $I)
	{
		$I->wantToTest('that attachments are displayed in the list.');

		$I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png']);

		$I->amOnPage(AttachmentsListPage::$url);

		$I->seeNumberOfElements(AttachmentsListPage::$rootClass . ' .dp-attachment', 2);
	}
}
