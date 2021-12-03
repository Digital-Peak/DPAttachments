<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Step\Acceptance;

class Attachment extends \AcceptanceTester
{
	public const ATTACHMENT_DIR = '/images/com_content.article/';

	private $user = null;

	public function _inject(User $user)
	{
		$this->user = $user;
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
			'publish_up'   => '0000-00-00 00:00:00',
			'publish_down' => '0000-00-00 00:00:00',
			'created_by'   => $this->user->getLoggedInUserId()
		];

		if (is_array($data)) {
			$attachment = array_merge($attachment, $data);
		}

		if (empty($attachment['title']) && !empty($attachment['path'])) {
			$attachment['title'] = $attachment['path'];
		}

		copy(codecept_data_dir($attachment['path']), $I->getConfiguration('home_dir') . self::ATTACHMENT_DIR . $attachment['path']);

		$attachment['id'] = $I->haveInDatabase('dpattachments', $attachment);

		return $attachment;
	}
}
