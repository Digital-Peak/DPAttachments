<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2022 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Acceptance\Views;

use Tests\Support\AcceptanceTester;
use Tests\Support\BasicDPAttachmentsCestClass;
use Tests\Support\Step\Article;
use Tests\Support\Step\Attachment;

class ArticleFormViewCest extends BasicDPAttachmentsCestClass
{
	public function _before(AcceptanceTester $I): void
	{
		parent::_before($I);

		$I->enablePlugin('plg_content_dpattachments');
	}

	public function canSeeAttachmentDetails(Attachment $I, Article $IA): void
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

	public function canSeeUploadFormInArticleFormWhenAdmin(Article $I): void
	{
		$I->wantToTest('that the upload form is displayed in an article form.');

		$article = $I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin('admin', 'admin');
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');

		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function canSeeUploadFormInArticleFormWhenUser(Article $I): void
	{
		$I->wantToTest('that the upload form is displayed in an article form when regular user.');

		$article = $I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin('manager', 'manager');
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');

		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function canSeeUploadFormInArticleFormForSpecificCategory(Article $I): void
	{
		$I->wantToTest('that the upload form is displayed in an article form for a specific category.');

		$I->doAdministratorLogin();

		$category = $I->createDPCategory('Test cat', 'com_content');
		$I->setExtensionParam('cat_ids', [$category], 'plg_content_dpattachments');

		$article = $I->createArticle(['title' => 'Test title', 'catid' => $category]);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');

		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function cantSeeUploadFormInArticleFormForSpecificCategory(Article $I): void
	{
		$I->wantToTest('that the upload form is displayed in an article form for a specific category.');

		$I->doAdministratorLogin();

		$category = $I->createDPCategory('Test cat', 'com_content');

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

	public function canUploadAttachmentOnArticleFormPageNotSaved(Article $I): void
	{
		$I->wantToTest('that an attachment can be uploaded to an article in the form for a new article.');

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit');
		$I->click('Attachments');

		$I->attachFile('.com-dpattachments-layout-form .dp-input__file', 'test.txt');
		$I->waitForElement('.dp-attachment');

		$I->see('test.txt');
		$I->seeElement('.dp-attachment__link');
		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article', 'item_id like' => 'tmp-%']);
	}

	public function canUploadAttachmentOnArticleFormPageSaved(Article $I): void
	{
		$I->wantToTest('that an attachment can be uploaded to an article in the form for an existing article.');

		$article = $I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit&a_id=' . $article['id']);
		$I->click('Attachments');

		$I->attachFile('.com-dpattachments-layout-form .dp-input__file', 'test.txt');
		$I->waitForElement('.dp-attachment');

		$I->see('test.txt');
		$I->seeElement('.dp-attachment__link');
		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article', 'item_id' => $article['id']]);
	}

	public function canUploadMultipleAttachmentOnArticleFormPage(Article $I): void
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
		$I->assertFileEquals(codecept_data_dir() . '/test.txt', $I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR . 'test.txt');
		$I->assertFileEquals(codecept_data_dir() . '/test.jpg', $I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR . 'test.jpg');
	}

	public function canUploadAttachmentOnArticleFormAndAssign(Article $I): void
	{
		$I->wantToTest('that an attachment can be uploaded to an article in the form for a new article and correctly be assigned.');

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&task=article.edit');
		$I->fillField('#jform_title', 'Test article');
		$I->click('Attachments');

		$I->attachFile('.com-dpattachments-layout-form .dp-input__file', 'test.txt');
		$I->waitForElement('.dp-attachment');
		$I->click('Save');
		$I->click('Attachments');

		$I->see('test.txt');
		$I->seeElement('.dp-attachment__link');
		$I->dontSeeInDatabase('dpattachments', ['context' => 'com_content.article', 'item_id like' => 'tmp-%']);
	}

	public function canTrashAttachment(Attachment $I, Article $IA): void
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
}
