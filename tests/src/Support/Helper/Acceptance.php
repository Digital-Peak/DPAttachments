<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Support\Helper;

use Codeception\Module;
use DigitalPeak\Module\DPBrowser;
use DigitalPeak\Support\Page\Joomla\Administrator\MediaPage;

class Acceptance extends Module
{
	public function amOnPage($link, $checkForErrors = true, $clearSession = true): void
	{
		/** @var DPBrowser $browser */
		$browser = $this->getModule(DPBrowser::class);

		// Temporary fix till https://github.com/joomla/joomla-cms/issues/40690 is solved
		if (str_contains((string)$link, MediaPage::$mediaUrl . '&path=')) {
			parse_str((string)$link, $result);
			$dir = addslashes(json_encode(['selectedDirectory' => $result['path']]));
			$browser->executeJS('sessionStorage.setItem("joomla.mediamanager", "' . $dir . '")');
			$clearSession = false;
		}

		$browser->amOnPage($link, $checkForErrors, $clearSession);
	}
}
