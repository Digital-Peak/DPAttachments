<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Step\Acceptance\Article;
use Step\Acceptance\Attachment;

class AttachmentFormViewCest extends \BasicDPAttachmentsCestClass
{
	private $url = '/index.php?option=com_dpattachments&task=attachment.edit&a_id=';

	public function canOpenFormFromArticle(Attachment $I, Article $IA)
	{
		$I->wantToTest('that the attachment form can be opened from an article view.');

		$article    = $IA->createArticle(['title' => 'Test title']);
		$attachment = $I->createAttachment(['path' => 'test.txt', 'item_id' => $article['id']]);

		$I->doFrontEndLogin();
		$I->amOnPage('/index.php?option=com_content&view=article&id=' . $article['id']);
		$I->click('Edit', '.com-dpattachments-layout-attachments');

		$I->seeElement('.com-dpattachments-attachment-form');
		$I->seeInField('#jform_path', 'test.txt');
		$I->seeInCurrentUrl('a_id=' . $attachment['id']);
	}

	public function canEditAttachment(Attachment $I, Article $IA)
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
