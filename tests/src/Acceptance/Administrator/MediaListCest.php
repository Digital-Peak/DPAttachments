<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2022 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Acceptance\Administrator;

use Codeception\Util\FileSystem;
use DigitalPeak\Support\Page\Joomla\Administrator\MediaPage;
use Tests\Support\AcceptanceTester;
use Tests\Support\BasicDPAttachmentsCestClass;
use Tests\Support\Step\Attachment;

class MediaListCest extends BasicDPAttachmentsCestClass
{
	public function _before(AcceptanceTester $I)
	{
		parent::_before($I);

		$I->doAdministratorLogin();

		if (is_dir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . '/images/test')) {
			(new FileSystem())->deleteDir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . '/images/test');
		}

		mkdir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . '/images/test');
		mkdir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . '/images/test/delete');
		copy(codecept_data_dir('test.jpg'), $I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . '/images/test/test.jpg');
	}

	public function _after(AcceptanceTester $I)
	{
		parent::_after($I);

		if (is_dir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . '/images/test')) {
			(new FileSystem())->deleteDir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . '/images/test');
		}
	}

	public function canDeleteFile(Attachment $I)
	{
		$I->wantToTest('that it is possible to delete a file in the media manager.');

		$I->amOnPage(MediaPage::$mediaUrl . '&path=local-images:/test');
		$I->waitForElement('.media-browser-image');
		$I->moveMouseOver('.media-browser-image .media-browser-item-info');
		$I->click('.media-browser-image .action-toggle');
		$I->click('.media-browser-image .action-delete');
		$I->click('#media-delete-item');
		$I->waitForText('Item deleted');

		$I->dontSeeFileFound($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . '/images/test/test.jpg');
	}

	public function canDeleteFolder(Attachment $I)
	{
		$I->wantToTest('that it is possible to delete a folder in the media manager.');

		$I->amOnPage(MediaPage::$mediaUrl . '&path=local-images:/test');
		$I->waitForElement('.media-browser-item-directory');
		$I->moveMouseOver('.media-browser-item-directory .media-browser-item-info');
		$I->click('.media-browser-item-directory .action-toggle');
		$I->click('.media-browser-item-directory .action-delete');
		$I->click('#media-delete-item');
		$I->waitForText('Item deleted');

		$I->dontSeeFileFound($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . '/images/test/delete');
	}
}
