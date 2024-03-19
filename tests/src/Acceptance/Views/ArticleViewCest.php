<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Acceptance\Views;

use Tests\Support\AcceptanceTester;
use Tests\Support\BasicDPAttachmentsCestClass;
use Tests\Support\Step\Article;
use Tests\Support\Step\Attachment;

class ArticleViewCest extends BasicDPAttachmentsCestClass
{
	public function _before(AcceptanceTester $I): void
	{
		parent::_before($I);

		$I->enablePlugin('plg_content_dpattachments');
	}

	public function canSeeAttachmentDetails(Attachment $I, Article $IA): void
	{
		$I->wantToTest('that the attachment details are shown.');

		$article = $IA->createArticle(['title' => 'Test title']);
		$I->createAttachment(['path' => 'test.txt', 'item_id' => $article['id']]);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&view=article&id=' . $article['id']);

		$I->see('test.txt');
		$I->see('Edit');
		$I->see('Trash');
	}

	public function canSeeUploadFormInArticleWhenAdmin(Article $I): void
	{
		$I->wantToTest('that the upload form is displayed in an article.');

		$I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin('admin', 'admin');
		$I->amOnPage('');

		$I->see('Test title');
		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function canSeeUploadFormInArticleWhenUser(Article $I): void
	{
		$I->wantToTest('that the upload form is displayed in an article when regular user.');

		$I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin('manager', 'manager');
		$I->amOnPage('');

		$I->see('Test title');
		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function cantSeeUploadFormInArticleWhenUser(Article $I): void
	{
		$I->wantToTest('that the upload form is not displayed in an article when regular user.');

		$I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin('user', 'user');
		$I->amOnPage('');

		$I->see('Test title');
		$I->dontSeeElement('.com-dpattachments-layout-form');
	}

	public function cantSeeUploadFormInArticleWhenGuest(Article $I): void
	{
		$I->wantToTest('that the upload form is not displayed in an article.');

		$I->createArticle(['title' => 'Test title']);

		$I->amOnPage('');

		$I->see('Test title');
		$I->dontSeeElement('.com-dpattachments-layout-form');
	}

	public function canSeeUploadFormInArticleForSpecificCategory(Article $I): void
	{
		$I->wantToTest('that the upload form is displayed in an article for a specific category.');

		$I->doAdministratorLogin();

		$category = $I->createDPCategory('Test cat', 'com_content');
		$I->setExtensionParam('cat_ids', [$category], 'plg_content_dpattachments');

		$I->createArticle(['title' => 'Test title', 'catid' => $category]);

		$I->doFrontEndLogin();
		$I->amOnPage('');

		$I->see('Test title');
		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function cantSeeUploadFormInArticleForSpecificCategory(Article $I): void
	{
		$I->wantToTest('that the upload form is displayed in an article for a specific category.');

		$I->doAdministratorLogin();

		$category = $I->createDPCategory('Test cat', 'com_content');

		$I->setExtensionParam(
			'cat_ids',
			[$I->grabFromDatabase('categories', 'id', ['title' => 'Uncategorised', 'extension' => 'com_content'])],
			'plg_content_dpattachments'
		);

		$I->createArticle(['title' => 'Test title', 'catid' => $category]);

		$I->doFrontEndLogin();
		$I->amOnPage('');

		$I->see('Test title');
		$I->dontSeeElement('.com-dpattachments-layout-form');
	}

	public function canUploadAttachmentOnFrontPage(Article $I): void
	{
		$I->wantToTest('that an attachment can be uploaded to an article.');

		$I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin();
		$I->amOnPage('');

		$I->attachFile('.com-dpattachments-layout-form .dp-input__file', 'test.txt');
		$I->waitForElement('.dp-attachment');

		$I->see('test.txt');
		$I->see('by Admin');
		$I->seeElement('.dp-attachment__link');
		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article']);
	}

	public function canUploadAttachmentOnArticleDetailsPage(Article $I): void
	{
		$I->wantToTest('that an attachment can be uploaded to an article.');

		$article = $I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&view=article&id=' . $article['id']);

		$I->attachFile('.com-dpattachments-layout-form .dp-input__file', 'test.txt');
		$I->waitForElement('.dp-attachment');

		$I->see('test.txt');
		$I->seeElement('.dp-attachment__link');
		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article']);
	}

	public function canUploadMultipleAttachmentOnArticleDetailsPage(Article $I): void
	{
		$I->wantToTest('that an attachment can be uploaded to an article.');

		$article = $I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&view=article&id=' . $article['id']);

		$I->attachFile('.com-dpattachments-layout-form .dp-input__file', 'test.txt');
		$I->waitForText('test.txt');
		$I->attachFile('.com-dpattachments-layout-form .dp-input__file', 'test.jpg');
		$I->waitForText('test.jpg');

		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article', 'path' => 'test.txt']);
		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article', 'path' => 'test.jpg']);
		$I->assertFileEquals(codecept_data_dir() . '/test.txt', $I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR . 'test.txt');
		$I->assertFileEquals(codecept_data_dir() . '/test.jpg', $I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR . 'test.jpg');
	}

	public function canTrashAttachment(Attachment $I, Article $IA): void
	{
		$I->wantToTest('that an attachment can be trashed.');

		$article = $IA->createArticle(['title' => 'Test title']);
		$I->createAttachment(['path' => 'test.txt', 'item_id' => $article['id']]);

		$I->doFrontEndLogin();
		$I->amOnPage('index.php?option=com_content&view=article&id=' . $article['id']);
		$I->click('Trash');
		$I->wait(2);

		$I->dontSee('test.txt');
		$I->seeInDatabase('dpattachments', ['context' => 'com_content.article', 'path' => 'test.txt', 'state' => -2]);
	}
}
