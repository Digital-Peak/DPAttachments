<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Docs\Acceptance\Misc;

use Docs\Support\AcceptanceTester;
use Docs\Support\BasicDPAttachmentsCestClass;

class LanguagesCest extends BasicDPAttachmentsCestClass
{
	public function screenshotsLanguageOverrides(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
		$I->amOnPage('/administrator/index.php?option=com_languages&view=overrides');
		$I->waitForText('Languages: Overrides');
		$I->clickJoomlaToolbarButton('Clear Cache');
		$I->selectOption('#language_client', 'English (United Kingdom) - Administrator');
		$I->clickJoomlaToolbarButton('New');
		$I->fillField('#jform_searchstring', 'filename');
		$I->waitForElementNotVisible('#refresh-status', 60);
		$I->click('Search');
		$I->wait(1);
		$I->waitForElementVisible('.language-results', 60);
		$I->makeVisible('.language-results');
		$I->click('.language-results .list-group-item-action');

		$file = $I->makeScreenshot('misc/languages-override', 'body', [940, 1020], false, false);
		$I->drawRectangle($file, [70, 430, 470, 520]);
	}
}
