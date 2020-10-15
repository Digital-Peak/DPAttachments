<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2020 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Step\Acceptance;

class User extends \AcceptanceTester
{
	/**
	 * Get logged in user data.
	 *
	 * @return array
	 */
	public function getLoggedInUserId()
	{
		return $this->grabFromDatabase('users', 'id', ['username' => 'admin']);
	}
}
