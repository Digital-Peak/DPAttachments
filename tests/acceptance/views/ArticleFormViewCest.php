<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2022 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Step\Acceptance\Article;
use Step\Acceptance\Attachment;

class ArticleFormViewCest extends \BasicDPAttachmentsCestClass
{
	public function _before(\AcceptanceTester $I)
	{
		parent::_before($I);

		$I->enablePlugin('plg_content_dpattachments');
	}

	public function canSeeAttachmentDetails(Attachment $I, Article $IA)
	{
		$I->wantToTest('that the attachment details are shown in the article form.');

		$article = $IA->createArticle(['title' => 'Test title']);
		$I->createAttachment(['path' => 'test.txt', 'item_id' => $article['id']]);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');

		$I->see('test.txt');
		$I->see('Edit');
		$I->see('Trash');
	}

	public function canSeeUploadFormInArticleFormWhenAdmin(Article $I)
	{
		$I->wantToTest('that the upload form is displayed in an article form.');

		$article = $I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin('admin', 'admin');
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');

		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function canSeeUploadFormInArticleFormWhenUser(Article $I)
	{
		$I->wantToTest('that the upload form is displayed in an article form when regular user.');

		$article = $I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin('manager', 'manager');
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');

		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function canSeeUploadFormInArticleFormForSpecificCategory(Article $I)
	{
		$I->wantToTest('that the upload form is displayed in an article form for a specific category.');

		$category = $I->createCat('Test cat');
		$I->setExtensionParam('cat_ids', [$category], 'plg_content_dpattachments');

		$article = $I->createArticle(['title' => 'Test title', 'catid' => $category]);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');

		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function cantSeeUploadFormInArticleFormForSpecificCategory(Article $I)
	{
		$I->wantToTest('that the upload form is displayed in an article form for a specific category.');

		$category = $I->createCat('Test cat');

		$I->setExtensionParam(
			'cat_ids',
			[$I->grabFromDatabase('categories', 'id', ['title' => 'Uncategorised', 'extension' => 'com_content'])],
			'plg_content_dpattachments'
		);

		$article = $I->createArticle(['title' => 'Test title', 'catid' => $category]);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);

		$I->dontSeeElement('Attachments');
	}

	public function canUploadAttachmentOnArticleFormPage(Article $I)
	{
		$I->wantToTest('that an attachment can be uploaded to an article in the form.');

		$article = $I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');

		$I->attachFile('.com-dpattachments-layout-form .dp-input__file', 'test.txt');
		$I->waitForElement('.dp-attachment');

		$I->see('test.txt');
		$I->seeElement('.dp-attachment__link');
		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article']);
	}

	public function canUploadMultipleAttachmentOnArticleFormPage(Article $I)
	{
		$I->wantToTest('that an attachment can be uploaded to an article in the form.');

		$article = $I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');

		$I->attachFile('.com-dpattachments-layout-form .dp-input__file', 'test.txt');
		$I->waitForText('test.txt');
		$I->attachFile('.com-dpattachments-layout-form .dp-input__file', 'test.jpg');
		$I->waitForText('test.jpg');

		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article', 'path' => 'test.txt']);
		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article', 'path' => 'test.jpg']);
		$I->assertFileEquals(codecept_data_dir() . '/test.txt', $I->getConfiguration('home_dir') . Attachment::ATTACHMENT_DIR . 'test.txt');
		$I->assertFileEquals(codecept_data_dir() . '/test.jpg', $I->getConfiguration('home_dir') . Attachment::ATTACHMENT_DIR . 'test.jpg');
	}

	public function canTrashAttachment(Attachment $I, Article $IA)
	{
		$I->wantToTest('that an attachment can be trashed.');

		$article = $IA->createArticle(['title' => 'Test title']);
		$I->createAttachment(['path' => 'test.txt', 'item_id' => $article['id']]);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');
		$I->click('Trash');
		$I->wait(2);

		$I->dontSee('test.txt');
		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article', 'path' => 'test.txt', 'state' => -2]);
	}

	public function canSeeInformationMessageWhenArticleIsNotSaved(Article $I)
	{
		$I->wantToTest('that an information message is shown when an article is not saved.');

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit');
		$I->click('Attachments');

		$I->see('Attachments can be uploaded when the form has been saved');
	}
}
