<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2021 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Acceptance\Views;

use Tests\Support\AcceptanceTester;
use Tests\Support\BasicDPAttachmentsCestClass;
use Tests\Support\Step\Article;
use Tests\Support\Step\Attachment;

class AttachmentFormViewCest extends BasicDPAttachmentsCestClass
{
	private string $url = '/index.php?option=com_dpattachments&task=attachment.edit&id=';

	public function _before(AcceptanceTester $I): void
	{
		parent::_before($I);

		$I->enablePlugin('plg_content_dpattachments');
	}

	public function canOpenFormFromArticle(Attachment $I, Article $IA): void
	{
		$I->wantToTest('that the attachment form can be opened from an article view.');

		$article = $IA->createArticle(['title' => 'Test title']);
		$I->createAttachment(['path' => 'test.txt', 'item_id' => $article['id']]);

		$I->doFrontEndLogin();
		$I->amOnPage('/index.php?option=com_content&view=article&id=' . $article['id']);
		$I->click('Edit', '.com-dpattachments-layout-attachments');

		$I->switchToIFrame('.dp-attachment-modal__content');
		$I->seeElement('.com-dpattachments-attachment-form');
		$I->seeInField('#jform_path', 'test.txt');
	}

	public function canEditAttachment(Attachment $I, Article $IA): void
	{
		$I->wantToTest('that an attachment can be edited.');

		$article    = $IA->createArticle(['title' => 'Test title']);
		$attachment = $I->createAttachment(['path' => 'test.txt', 'item_id' => $article['id']]);

		$I->doFrontEndLogin();
		$I->amOnPage($this->url . $attachment['id']);

		$I->seeElement('.com-dpattachments-attachment-form');
		$I->seeInField('#jform_path', 'test.txt');

		$I->fillField('#jform_title', 'Test edit');
		$I->click('Save');
		$I->waitForText('Item saved');
		$I->click('Test title');

		$I->see('Test edit');
	}
}
