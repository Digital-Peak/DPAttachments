<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2022 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Step\Acceptance\Attachment;

class TaskEditCest extends \BasicDPAttachmentsCestClass
{
	public function _before(\AcceptanceTester $I)
	{
		parent::_before($I);

		$I->enablePlugin('plg_content_dpattachments');
	}

	public function cantSeeAttachmentDetails(Attachment $I)
	{
		$I->wantToTest('that the attachment details are not shown in the task form.');

		$I->doAdministratorLogin(null, null, false);
		$I->amOnPage('/administrator/index.php?option=com_scheduler&view=select&layout=default');
		$I->click('Demo Task');

		$I->dontSee('Attachments');
	}
}