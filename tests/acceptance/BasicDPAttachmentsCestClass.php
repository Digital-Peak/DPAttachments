<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2020 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

class BasicDPAttachmentsCestClass
{
	public function _before(\AcceptanceTester $I)
	{
		$I->deleteFromDatabase('content', []);
		$I->deleteFromDatabase('content_frontpage', []);
		$I->deleteFromDatabase('dpattachments', []);
	}

	public function _after(\AcceptanceTester $I)
	{
		$I->checkForPhpNoticesOrWarnings();
		$I->checkForJsErrors();
	}

	public function _failed(AcceptanceTester $I)
	{
		$I->pause();
	}
}
