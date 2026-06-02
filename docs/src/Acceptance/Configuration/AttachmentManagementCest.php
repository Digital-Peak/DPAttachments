<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Docs\Acceptance\Misc;

use Docs\Support\BasicDPAttachmentsCestClass;
use Docs\Support\Step\Article;
use Docs\Support\Step\Attachment;

class AttachmentManagementCest extends BasicDPAttachmentsCestClass
{
	public function screenshotAttachmentList(Attachment $I, Article $IA): void
	{
		$article = $IA->createArticle(['title' => 'Demo article with attachments']);
		$I->createAttachment(['path' => 'joomla-logo.jpg', 'title' => 'Joomla Logo.jpg', 'item_id' => $article['id']]);
		$I->createAttachment(['path' => 'demo-attachment.png', 'title' => 'Demo Attachment.png', 'item_id' => $article['id']]);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&view=article&id=' . $article['id']);

		$I->makeScreenshot('configuration/attachments-list', 'body', [940, 700]);
	}

	public function screenshotAttachmentBackEndForm(Attachment $I, Article $IA): void
	{
		$article = $IA->createArticle(['title' => 'Demo article with attachments', 'alias' => 'demo-article-with-attachments']);
		$I->createAttachment(['path' => 'joomla-logo.jpg', 'title' => 'Joomla Logo.jpg', 'item_id' => $article['id']]);
		$I->createAttachment(['path' => 'demo-attachment.png', 'title' => 'Demo Attachment.png', 'item_id' => $article['id']]);

		$I->doAdministratorLogin();
		$I->amOnPage('/administrator/index.php?option=com_content&task=article.edit&id=' . $article['id']);
		$I->click('Attachments');

		$I->makeScreenshot('configuration/attachments-backend', 'body', [940, 730]);
	}

	public function screenshotAttachmentForm(Attachment $I, Article $IA): void
	{
		$article = $IA->createArticle(['title' => 'Demo article with attachments']);
		$I->createAttachment(['path' => 'joomla-logo.jpg', 'title' => 'Joomla Logo.jpg', 'item_id' => $article['id']]);
		$I->createAttachment(['path' => 'demo-attachment.png', 'title' => 'Demo Attachment.png', 'item_id' => $article['id']]);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&view=article&id=' . $article['id']);
		$I->click('Edit', '.com-dpattachments-layout-attachments');
		$I->waitForElementVisible('.dp-attachment-modal__content');

		$I->makeScreenshot('configuration/attachments-form', 'body', [940, 700]);
	}
}
