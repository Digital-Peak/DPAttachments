<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Support\Step;

use Tests\Support\AcceptanceTester;

class Attachment extends AcceptanceTester
{
	public const ARTICLES_ATTACHMENT_DIR = '/images/com_content.article/';

	public const CATEGORIES_ATTACHMENT_DIR = '/images/com_categories.category/';

	private ?Article $article = null;

	public function _inject(Article $article): void
	{
		$this->article = $article;
	}

	/**
	 * Creates an attachment in the database and returns the attachment data
	 * as array including the id of the new attachment.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function createAttachment($data = null)
	{
		$I = $this;

		$attachment = [
			'context'      => 'com_content.article',
			'state'        => 1,
			'access'       => 1,
			'publish_up'   => null,
			'publish_down' => null,
			'description'  => '',
			'created'      => (new \DateTime())->format('Y-m-d H:i:s'),
			'created_by'   => $I->grabFromDatabase('users', 'id', ['username' => 'admin']),
		];

		if (\is_array($data)) {
			$attachment = array_merge($attachment, $data);
		}

		if (empty($attachment['title']) && !empty($attachment['path'])) {
			$attachment['title'] = $attachment['path'];
		}

		if (empty($attachment['item_id'])) {
			$attachment['item_id'] = $this->article->createArticle(['title' => 'Test title'])['id'];
		}

		copy(codecept_data_dir($attachment['path']), $I->getConfiguration('home_dir', 'DigitalPeak\Module\DPBrowser') . self::ARTICLES_ATTACHMENT_DIR . $attachment['path']);

		$attachment['id'] = $I->haveInDatabase('dpattachments', $attachment);

		return $attachment;
	}
}
