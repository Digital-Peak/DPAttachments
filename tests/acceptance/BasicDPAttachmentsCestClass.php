<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Step\Acceptance\Attachment;

class BasicDPAttachmentsCestClass
{
	public function _before(\AcceptanceTester $I)
	{
		$I->updateInDatabase('extensions', ['params' => '{}'], ['name like' => '%dpattachments%']);

		$I->deleteFromDatabase('categories', ['id >' => 7]);
		$I->deleteFromDatabase('content', []);
		$I->deleteFromDatabase('content_frontpage', []);
		$I->deleteFromDatabase('dpattachments', []);

		if ($I->getConfiguration('joomla_version') == 4) {
			$I->deleteFromDatabase('workflow_associations', []);
		}

		$I->setExtensionParam('attachment_path', 'images');

		$I->getModule('Filesystem')->deleteDir($I->getConfiguration('home_dir') . Attachment::ATTACHMENT_DIR);

		mkdir($I->getConfiguration('home_dir') . Attachment::ATTACHMENT_DIR, 0777, true);
	}

	public function _after(\AcceptanceTester $I)
	{
		$I->getModule('Filesystem')->deleteDir($I->getConfiguration('home_dir') . Attachment::ATTACHMENT_DIR);

		$I->checkForPhpNoticesOrWarnings();
		$I->checkForJsErrors();
	}

	public function _failed(AcceptanceTester $I)
	{
		$I->pause();
	}
}
