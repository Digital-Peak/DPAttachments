<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Support;

use Tests\Support\Step\Attachment;

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

		$I->deleteDir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR);
		$I->deleteDir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::CATEGORIES_ATTACHMENT_DIR);

		mkdir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR, 0777, true);
		mkdir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::CATEGORIES_ATTACHMENT_DIR, 0777, true);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteDir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR);
		$I->deleteDir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::CATEGORIES_ATTACHMENT_DIR);

		$I->checkForPhpNoticesOrWarnings();
		$I->checkForJsErrors();
	}

	public function _failed(AcceptanceTester $I)
	{
		$I->pause();
	}
}
