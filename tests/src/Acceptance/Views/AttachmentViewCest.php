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

	public function canNotOpenUnpublishedAttachmentDetailsPage(Attachment $I): void
	{
		$I->wantToTest('that an unpublished attachment cannot be viewed by a guest.');

		$attachment = $I->createAttachment(['path' => 'test.txt', 'state' => 0]);

		$I->amOnPage($this->url . $attachment['id'], false);

		$I->see("You don't have permission to access this.");
		$I->dontSee('Test content');

		$this->runErrorChecks = false;
	}

	public function canOpenNotPublishedDownAttachmentDetailsPage(Attachment $I): void
	{
		$I->wantToTest('that a not yet published down attachment can be viewed by a guest.');

		$attachment = $I->createAttachment(['path' => 'test.txt', 'publish_down' => (new \DateTime('+1 day'))->format('Y-m-d')]);

		$I->amOnPage($this->url . $attachment['id']);

		$I->see('Test content');
		$I->dontSee("You don't have permission to access this.");
	}

	public function canNotOpenPublishedDownAttachmentDetailsPage(Attachment $I): void
	{
		$I->wantToTest('that a published down attachment cannot be viewed by a guest.');

		$attachment = $I->createAttachment(['path' => 'test.txt', 'publish_down' => (new \DateTime('-1 day'))->format('Y-m-d')]);

		$I->amOnPage($this->url . $attachment['id'], false);

		$I->see("You don't have permission to access this.");
		$I->dontSee('Test content');

		$this->runErrorChecks = false;
	}

	public function canOpenPublishedUpAttachmentDetailsPage(Attachment $I): void
	{
		$I->wantToTest('that a published up attachment can be viewed by a guest.');

		$attachment = $I->createAttachment(['path' => 'test.txt', 'publish_up' => (new \DateTime('-1 day'))->format('Y-m-d')]);

		$I->amOnPage($this->url . $attachment['id']);

		$I->see('Test content');
		$I->dontSee("You don't have permission to access this.");
	}

	public function canNotOpenNotPublishedUpAttachmentDetailsPage(Attachment $I): void
	{
		$I->wantToTest('that a not yet published up attachment cannot be viewed by a guest.');

		$attachment = $I->createAttachment(['path' => 'test.txt', 'publish_up' => (new \DateTime('+1 day'))->format('Y-m-d')]);

		$I->amOnPage($this->url . $attachment['id'], false);

		$I->see("You don't have permission to access this.");
		$I->dontSee('Test content');

		$this->runErrorChecks = false;
	}

	public function canOpenAttachmentDetailsPageRestrictedToRegisteredUsers(Attachment $I): void
	{
		$I->wantToTest('that an attachment restricted to registered users can be viewed by a logged in user.');

		$registeredLevel = $I->grabFromDatabase('viewlevels', 'id', ['title' => 'Registered']);
		$attachment      = $I->createAttachment(['path' => 'test.txt', 'access' => $registeredLevel]);

		$I->doFrontEndLogin('user', 'user');
		$I->amOnPage($this->url . $attachment['id']);

		$I->see('Test content');
		$I->dontSee("You don't have permission to access this.");
	}

	public function canNotOpenAttachmentDetailsPageRestrictedToRegisteredUsers(Attachment $I): void
	{
		$I->wantToTest('that an attachment restricted to registered users cannot be viewed by a guest.');

		$registeredLevel = $I->grabFromDatabase('viewlevels', 'id', ['title' => 'Registered']);
		$attachment      = $I->createAttachment(['path' => 'test.txt', 'access' => $registeredLevel]);

		$I->amOnPage($this->url . $attachment['id'], false);

		$I->see("You don't have permission to access this.");
		$I->dontSee('Test content');

		$this->runErrorChecks = false;
	}

	public function canDownloadRestrictedAttachment(Attachment $I): void
	{
		$I->wantToTest('that a restricted attachment can be downloaded by a logged in user.');

		$registeredLevel = $I->grabFromDatabase('viewlevels', 'id', ['title' => 'Registered']);
		$attachment      = $I->createAttachment(['path' => 'test.txt', 'access' => $registeredLevel]);

		$I->doFrontEndLogin('user', 'user');
		$I->amOnPage('/index.php?option=com_dpattachments&task=attachment.download&id=' . $attachment['id']);
		$I->wait(1);

		$I->seeFileFound('test.txt', $I->getConfiguration('downloads', 'DigitalPeak\Module\DPBrowser'));
	}

	public function canNotDownloadRestrictedAttachment(Attachment $I): void
	{
		$I->wantToTest('that a restricted attachment cannot be downloaded by a guest.');

		$registeredLevel = $I->grabFromDatabase('viewlevels', 'id', ['title' => 'Registered']);
		$attachment      = $I->createAttachment(['path' => 'test.txt', 'access' => $registeredLevel]);

		$I->amOnPage('/index.php?option=com_dpattachments&task=attachment.download&id=' . $attachment['id'], false);
		$I->wait(1);

		$I->dontSeeFileFound('test.txt', $I->getConfiguration('downloads', 'DigitalPeak\Module\DPBrowser'));
	}

	public function canNotSeeUnescapedCSVHeader(Attachment $I): void
	{
		$I->wantToTest('that a malicious csv header value is escaped and not executed.');

		$attachment = $I->createAttachment(['path' => 'test-xss.csv']);

		$I->amOnPage($this->url . $attachment['id']);

		$I->seeInSource('&lt;script&gt;window.dpXssExecuted = true&lt;/script&gt;');
		$I->dontSeeInSource('<script>window.dpXssExecuted = true</script>');
		$I->assertFalse($I->executeJS('return window.dpXssExecuted === true'));
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
