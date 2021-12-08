<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Step\Acceptance\Article;

class ArticleViewCest extends \BasicDPAttachmentsCestClass
{
	public function canSeeUploadFormInArticle(Article $I)
	{
		$I->wantToTest('that the upload form is displayed in an article.');

		$I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin();
		$I->amOnPage('');

		$I->see('Test title');
		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function cantSeeUploadFormInArticle(Article $I)
	{
		$I->wantToTest('that the upload form is not displayed in an article.');

		$I->createArticle(['title' => 'Test title']);

		$I->amOnPage('');

		$I->see('Test title');
		$I->dontSeeElement('.com-dpattachments-layout-form');
	}

	public function canSeeUploadFormInArticleForSpecificCategory(Article $I)
	{
		$I->wantToTest('that the upload form is displayed in an article for a specific category.');

		$category = $I->createCat('Test cat');
		$I->setExtensionParam('cat_ids', [$category], 'plg_content_dpattachments');

		$I->createArticle(['title' => 'Test title', 'catid' => $category]);

		$I->doFrontEndLogin();
		$I->amOnPage('');

		$I->see('Test title');
		$I->seeElement('.com-dpattachments-layout-form');
	}

	public function cantSeeUploadFormInArticleForSpecificCategory(Article $I)
	{
		$I->wantToTest('that the upload form is displayed in an article for a specific category.');

		$category = $I->createCat('Test cat');

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

	public function canUploadAttachment(Article $I)
	{
		$I->wantToTest('that an attachment can be uploaded to an article.');

		$I->createArticle(['title' => 'Test title']);

		$I->doFrontEndLogin();
		$I->amOnPage('');

		$I->attachFile('.com-dpattachments-layout-form__form .dp-input__file', 'test.txt');
		$I->waitForElement('.dp-attachment');

		$I->see('test.txt');
		$I->seeElement('.dp-attachment__link');
	}
}
