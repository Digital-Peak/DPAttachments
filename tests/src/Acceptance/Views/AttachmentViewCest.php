<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Acceptance\Views;

use Codeception\Example;
use Tests\Support\BasicDPAttachmentsCestClass;
use Tests\Support\Step\Attachment;

class AttachmentViewCest extends BasicDPAttachmentsCestClass
{
	private string $url = '/index.php?option=com_dpattachments&view=attachment&id=';

	/**
	 * @dataProvider getImageFiles
	 */
	public function canOpenImageAttachmentDetailsPage(Attachment $I, Example $provider): void
	{
		$I->wantToTest('that an image attachment can be displayed.');

		$attachment = $I->createAttachment(['path' => 'test.' . $provider['extension']]);

		$I->amOnPage($this->url . $attachment['id']);

		$I->see('test.' . $provider['extension']);
		$I->seeElement('.com-dpattachments-attachment-' . $provider['extension']);
		$I->seeElement('.com-dpattachments-attachment__content');
		$I->seeElement('img[src$="' . Attachment::ARTICLES_ATTACHMENT_DIR . 'test.' . $provider['extension'] . '"]');
	}

	/**
	 * @dataProvider getTextFiles
	 */
	public function canOpenTextAttachmentDetailsPage(Attachment $I, Example $provider): void
	{
		$I->wantToTest('that a text attachment can be displayed.');

		$attachment = $I->createAttachment(['path' => 'test.' . $provider['extension']]);

		$I->amOnPage($this->url . $attachment['id']);

		$I->see('test.' . $provider['extension']);
		$I->see('Test content');
		$I->seeElement('.com-dpattachments-attachment-' . $provider['extension']);
		$I->seeElement('.com-dpattachments-attachment__content');
	}

	protected function getImageFiles(): array
	{
		return [
			['extension' => 'gif'],
			['extension' => 'png'],
			['extension' => 'jpeg'],
			['extension' => 'jpg']
		];
	}

	protected function getTextFiles(): array
	{
		return [
			['extension' => 'txt'],
			['extension' => 'csv'],
			['extension' => 'patch'],
		];
	}
}
