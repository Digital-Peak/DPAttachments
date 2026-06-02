<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Docs\Support;

use Docs\Support\Step\Attachment;

class BasicDPAttachmentsCestClass
{
	public function _before(AcceptanceTester $I): void
	{
		$I->deleteDir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR);
		$I->deleteDir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::CATEGORIES_ATTACHMENT_DIR);

		mkdir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR, 0777, true);
		mkdir($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::CATEGORIES_ATTACHMENT_DIR, 0777, true);
	}

	public function _failed(AcceptanceTester $I): void
	{
		$I->pause();
	}
}
