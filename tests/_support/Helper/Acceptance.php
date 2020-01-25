<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module\JoomlaBrowser;

class Acceptance extends \Codeception\Module
{
	public function getConfiguration($element = null)
	{
		if (is_null($element)) {
			throw new InvalidArgumentException('empty value or non existing element was requested from configuration');
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
			$this->assertNotEquals('SEVERE', $log['level'], 'Some error in JavaScript: ' . json_encode($log));
		}
	}
}
