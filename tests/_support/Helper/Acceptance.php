<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Helper;

use Codeception\Module\JoomlaBrowser;

class Acceptance extends \Codeception\Module
{
	public function getConfiguration($element = null)
	{
		if (is_null($element)) {
			throw new \InvalidArgumentException('empty value or non existing element was requested from configuration');
		}

		return $this->config[$element];
	}

	public function amOnPage($link)
	{
		/** @var JoomlaBrowser $browser */
		$browser = $this->getModule('JoomlaBrowser');
		$browser->amOnPage($link);

		$browser->checkForPhpNoticesOrWarnings();
		$this->checkForJsErrors();
	}

	public function checkForJsErrors()
	{
		$logs = $this->getModule('JoomlaBrowser')->webDriver->manage()->getLog('browser');
		foreach ($logs as $log) {
			// Only look for internal JS errors
			if (strpos($log['message'], $this->getModule('JoomlaBrowser')->_getConfig()['url']) !== 0) {
				continue;
			}

			// J4 throws some CORS warnings
			if (strpos($log['message'], 'The Cross-Origin-Opener-Policy header has been ignored') !== 0) {
				continue;
			}

			$this->assertNotEquals('SEVERE', $log['level'], 'Some error in JavaScript: ' . json_encode($log));
		}
	}

	public function setExtensionParam($key, $value, $extension = 'com_dpattachments')
	{
		$db     = $this->getModule('Helper\\JoomlaDb');
		$params = $db->grabFromDatabase('extensions', 'params', ['name' => $extension]);

		$params       = json_decode($params);
		$params->$key = $value;
		$db->updateInDatabase('extensions', ['params' => json_encode($params)], ['name' => $extension]);
	}

	public function createCat($title)
	{
		/** @var JoomlaBrowser $browser */
		$I = $this->getModule('JoomlaBrowser');

		$I->doAdministratorLogin(null, null, false);
		$I->amOnPage('administrator/index.php?option=com_categories&extension=com_content');
		$I->click('New');
		$I->fillField(['id' => 'jform_title'], $title);
		$I->click('Save & Close');

		$db = $this->getModule('Helper\\JoomlaDb');

		return $db->grabFromDatabase('categories', 'id', ['title' => $title, 'extension' => 'com_content']);
	}

	public function getModule($name)
	{
		if ($name === 'JoomlaBrowser' && $this->getConfiguration('joomla_version') == 4) {
			$name = 'Joomla\Browser\JoomlaBrowser';
		}

		return parent::getModule($name);
	}
}
