<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2020 Digital Peak. All rights reserved.
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
