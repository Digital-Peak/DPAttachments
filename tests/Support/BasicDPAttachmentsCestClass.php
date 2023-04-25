<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Tests\Support;

use Tests\Support\Step\Acceptance\Attachment;

class BasicDPAttachmentsCestClass
{
	public function _before(AcceptanceTester $I)
	{
		$I->updateInDatabase('extensions', ['params' => '{}'], ['name like' => '%dpattachments%']);

		$I->deleteFromDatabase('categories', ['id >' => 7]);
		$I->deleteFromDatabase('content', []);
		$I->deleteFromDatabase('content_frontpage', []);
		$I->deleteFromDatabase('workflow_associations', []);
		$I->deleteFromDatabase('dpattachments', []);

		$I->setExtensionParam('attachment_path', 'images', 'com_dpattachments');

		$I->deleteDir($I->getConfiguration('home_dir') . Attachment::ARTICLES_ATTACHMENT_DIR);
		$I->deleteDir($I->getConfiguration('home_dir') . Attachment::CATEGORIES_ATTACHMENT_DIR);

		mkdir($I->getConfiguration('home_dir') . Attachment::ARTICLES_ATTACHMENT_DIR, 0777, true);
		mkdir($I->getConfiguration('home_dir') . Attachment::CATEGORIES_ATTACHMENT_DIR, 0777, true);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteDir($I->getConfiguration('home_dir') . Attachment::ARTICLES_ATTACHMENT_DIR);
		$I->deleteDir($I->getConfiguration('home_dir') . Attachment::CATEGORIES_ATTACHMENT_DIR);

		$I->dontSeeInPageSource('Deprecated:');
		$I->dontSeeInPageSource('<b>Deprecated</b>:');
		$I->checkForPhpNoticesOrWarnings();
		$I->checkForJsErrors();
	}

	public function _failed(AcceptanceTester $I)
	{
		$I->pause();
	}
}
