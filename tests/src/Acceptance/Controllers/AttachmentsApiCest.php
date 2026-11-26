<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Acceptance\Controllers;

use Codeception\Util\HttpCode;
use Tests\Support\APIDPAttachmentsCestClass;
use Tests\Support\Step\Article;
use Tests\Support\Step\Attachment;

class AttachmentsApiCest extends APIDPAttachmentsCestClass
{
	public function canSeeAttachmentsList(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be fetched by webservices.');

		$I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png']);

		$I->sendGet('/dpattachments/attachments');

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'test.jpg', 'context' => 'com_content.article']);
		$I->seeResponseContainsJson(['title' => 'test.png']);
	}

	public function canFilterAttachmentsListByItem(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be filtered by item.');

		$attachment = $I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png']);

		$I->sendGet('/dpattachments/attachments?filter[context]=com_content.article&filter[item_id]=' . $attachment['item_id']);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'test.jpg', 'item_id' => (string)$attachment['item_id']]);
		$I->dontSeeResponseContainsJson(['title' => 'test.png']);
	}

	public function cantSeeTrashedAttachmentsInList(Attachment $I): void
	{
		$I->wantToTest('that trashed attachments are not in the list by default.');

		$I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png', 'state' => -2]);

		$I->sendGet('/dpattachments/attachments');

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'test.jpg']);
		$I->dontSeeResponseContainsJson(['title' => 'test.png']);
	}

	public function canFilterAttachmentsListByContext(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be filtered by context.');

		$I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png', 'context' => 'com_categories.category', 'item_id' => 2]);

		$I->sendGet('/dpattachments/attachments', ['filter' => ['context' => 'com_categories.category']]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'test.png', 'context' => 'com_categories.category']);
		$I->dontSeeResponseContainsJson(['title' => 'test.jpg']);
	}

	public function canFilterAttachmentsListByState(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be filtered by state.');

		$I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png', 'state' => 0]);
		$I->createAttachment(['path' => 'test.gif', 'state' => -2]);

		$I->sendGet('/dpattachments/attachments', ['filter' => ['state' => 0]]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'test.png', 'state' => 0]);
		$I->dontSeeResponseContainsJson(['title' => 'test.jpg']);
		$I->dontSeeResponseContainsJson(['title' => 'test.gif']);

		$I->sendGet('/dpattachments/attachments', ['filter' => ['state' => -2]]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'test.gif', 'state' => -2]);
		$I->dontSeeResponseContainsJson(['title' => 'test.jpg']);
		$I->dontSeeResponseContainsJson(['title' => 'test.png']);
	}

	public function canFilterAttachmentsListByAccess(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be filtered by access level.');

		$I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png', 'access' => 3]);

		$I->sendGet('/dpattachments/attachments', ['filter' => ['access' => 3]]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'test.png', 'access' => 3]);
		$I->dontSeeResponseContainsJson(['title' => 'test.jpg']);
	}

	public function canFilterAttachmentsListByAuthor(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be filtered by author.');

		$I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png', 'created_by' => 999]);

		$I->sendGet('/dpattachments/attachments', ['filter' => ['author' => 999]]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'test.png', 'created_by' => 999]);
		$I->dontSeeResponseContainsJson(['title' => 'test.jpg']);
	}

	public function canSearchAttachmentsList(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be searched in title and description.');

		$I->createAttachment(['path' => 'test.jpg', 'title' => 'Holiday picture']);
		$I->createAttachment(['path' => 'test.png', 'description' => 'A holiday screenshot']);
		$I->createAttachment(['path' => 'test.gif']);

		$I->sendGet('/dpattachments/attachments', ['filter' => ['search' => 'holiday']]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'Holiday picture']);
		$I->seeResponseContainsJson(['title' => 'test.png']);
		$I->dontSeeResponseContainsJson(['title' => 'test.gif']);
	}

	public function canSearchAttachmentsListById(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be searched by ID.');

		$attachment = $I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png']);

		$I->sendGet('/dpattachments/attachments', ['filter' => ['search' => 'id:' . $attachment['id']]]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->assertSame([(int)$attachment['id']], $I->grabDataFromResponseByJsonPath('$.data[*].attributes.id'));
	}

	public function canSearchAttachmentsListByAuthor(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be searched by author name.');

		$I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png', 'created_by' => 999]);

		$I->sendGet('/dpattachments/attachments', ['filter' => ['search' => 'author:admin']]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'test.jpg']);
		$I->dontSeeResponseContainsJson(['title' => 'test.png']);
	}

	public function canOrderAttachmentsList(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be ordered.');

		$I->createAttachment(['path' => 'test.jpg', 'title' => 'B title']);
		$I->createAttachment(['path' => 'test.png', 'title' => 'A title']);
		$I->createAttachment(['path' => 'test.gif', 'title' => 'C title']);

		$I->sendGet('/dpattachments/attachments', ['list' => ['ordering' => 'a.title', 'direction' => 'asc']]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->assertSame(['A title', 'B title', 'C title'], $I->grabDataFromResponseByJsonPath('$.data[*].attributes.title'));

		$I->sendGet('/dpattachments/attachments', ['list' => ['ordering' => 'a.title', 'direction' => 'desc']]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->assertSame(['C title', 'B title', 'A title'], $I->grabDataFromResponseByJsonPath('$.data[*].attributes.title'));
	}

	public function canPaginateAttachmentsList(Attachment $I): void
	{
		$I->wantToTest('that the attachments list can be paginated.');

		$I->createAttachment(['path' => 'test.jpg', 'title' => 'A title']);
		$I->createAttachment(['path' => 'test.png', 'title' => 'B title']);
		$I->createAttachment(['path' => 'test.gif', 'title' => 'C title']);

		$I->sendGet('/dpattachments/attachments', ['list' => ['ordering' => 'a.title'], 'page' => ['limit' => 2, 'offset' => 1]]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->assertSame(['B title', 'C title'], $I->grabDataFromResponseByJsonPath('$.data[*].attributes.title'));
		$I->seeResponseContainsJson(['meta' => ['total-pages' => 2]]);
	}

	public function canSeeSingleAttachment(Attachment $I): void
	{
		$I->wantToTest('that the attachment details can be fetched by webservices.');

		$attachment = $I->createAttachment(['path' => 'test.jpg']);
		$I->createAttachment(['path' => 'test.png']);

		$I->sendGet('/dpattachments/attachments/' . $attachment['id']);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'test.jpg', 'path' => 'test.jpg']);
		$I->seeResponseContains('task=attachment.download');
		$I->dontSeeResponseContainsJson(['title' => 'test.png']);
	}

	public function cantSeeNotExistingAttachment(Attachment $I): void
	{
		$I->wantToTest('that no attachment details can be fetched when it does not exist.');

		$attachment = $I->createAttachment(['path' => 'test.jpg']);

		$I->sendGet('/dpattachments/attachments/' . ($attachment['id'] + 1));

		$I->seeResponseCodeIs(HttpCode::NOT_FOUND);
	}

	public function canCreateAttachment(Attachment $I, Article $IA): void
	{
		$I->wantToTest('that an attachment can be created by webservices.');

		$article = $IA->createArticle(['title' => 'Test title']);

		$I->sendPost('/dpattachments/attachments', [
			'context'     => 'com_content.article',
			'item_id'     => $article['id'],
			'title'       => 'API attachment',
			'description' => 'Uploaded through the API',
			'path'        => 'test.txt',
			'content'     => base64_encode((string)file_get_contents(codecept_data_dir('test.txt')))
		]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'API attachment', 'path' => 'test.txt']);
		$I->seeInDatabase('dpattachments', [
			'context' => 'com_content.article',
			'item_id' => $article['id'],
			'title'   => 'API attachment',
			'path'    => 'test.txt',
			'size'    => filesize(codecept_data_dir('test.txt'))
		]);
		$I->assertFileEquals(
			codecept_data_dir('test.txt'),
			$I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR . 'test.txt'
		);
	}

	public function cantCreateAttachmentWithInvalidExtension(Attachment $I, Article $IA): void
	{
		$I->wantToTest('that an attachment with an invalid extension can not be created by webservices.');

		$article = $IA->createArticle(['title' => 'Test title']);

		$I->sendPost('/dpattachments/attachments', [
			'context' => 'com_content.article',
			'item_id' => $article['id'],
			'path'    => 'test.exe',
			'content' => base64_encode('test')
		]);

		$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
		$I->dontSeeInDatabase('dpattachments', ['path' => 'test.exe']);
	}

	public function cantCreateAttachmentWithoutContent(Attachment $I, Article $IA): void
	{
		$I->wantToTest('that an attachment without content can not be created by webservices.');

		$article = $IA->createArticle(['title' => 'Test title']);

		$I->sendPost('/dpattachments/attachments', [
			'context' => 'com_content.article',
			'item_id' => $article['id'],
			'path'    => 'test.txt'
		]);

		$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
		$I->dontSeeInDatabase('dpattachments', ['path' => 'test.txt']);
	}

	public function canUpdateAttachment(Attachment $I): void
	{
		$I->wantToTest('that an attachment can be updated by webservices.');

		$attachment = $I->createAttachment(['path' => 'test.jpg']);

		$I->sendPatch('/dpattachments/attachments/' . $attachment['id'], ['title' => 'Test title edited', 'context' => 'com_foo.bar']);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['title' => 'Test title edited', 'context' => 'com_content.article']);
		$I->seeInDatabase('dpattachments', ['id' => $attachment['id'], 'title' => 'Test title edited', 'context' => 'com_content.article']);
	}

	public function canReplaceAttachmentFile(Attachment $I): void
	{
		$I->wantToTest('that the file of an attachment can be replaced by webservices.');

		$attachment = $I->createAttachment(['path' => 'test.txt']);

		$I->sendPatch('/dpattachments/attachments/' . $attachment['id'], ['content' => base64_encode('replaced content')]);

		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeInDatabase('dpattachments', ['id' => $attachment['id'], 'path' => 'test.txt', 'title' => 'test.txt', 'size' => 16]);
		$I->assertStringEqualsFile(
			$I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR . 'test.txt',
			'replaced content'
		);
	}

	public function cantDeleteNotTrashedAttachment(Attachment $I): void
	{
		$I->wantToTest('that an attachment which is not trashed can not be deleted by webservices.');

		$attachment = $I->createAttachment(['path' => 'test.jpg']);

		$I->sendDelete('/dpattachments/attachments/' . $attachment['id']);

		$I->seeResponseCodeIs(HttpCode::CONFLICT);
		$I->seeInDatabase('dpattachments', ['id' => $attachment['id']]);
	}

	public function canDeleteAttachment(Attachment $I): void
	{
		$I->wantToTest('that an attachment can be deleted by webservices.');

		$attachment = $I->createAttachment(['path' => 'test.jpg', 'state' => -2]);

		$I->sendDelete('/dpattachments/attachments/' . $attachment['id']);

		$I->seeResponseCodeIs(HttpCode::NO_CONTENT);
		$I->dontSeeInDatabase('dpattachments', ['id' => $attachment['id']]);
		$I->assertFileDoesNotExist($I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . Attachment::ARTICLES_ATTACHMENT_DIR . 'test.jpg');
	}
}
