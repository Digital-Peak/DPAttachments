<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2026 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Support;

class APIDPAttachmentsCestClass extends BasicDPAttachmentsCestClass
{
	public function _before(AcceptanceTester $I): void
	{
		parent::_before($I);

		$I->enablePlugin('plg_webservices_dpattachments');

		$I->deleteFromDatabase('user_profiles', []);

		$I->haveInDatabase(
			'user_profiles',
			['user_id' => 42, 'profile_key' => 'joomlatoken.enabled', 'profile_value' => 1]
		);
		$I->haveInDatabase(
			'user_profiles',
			['user_id' => 42, 'profile_key' => 'joomlatoken.token', 'profile_value' => 'T0yovLcSnxCIJ2HZkgtx+0TIa+46VpsCfkX+vIulvWk=']
		);

		$I->amBearerAuthenticated('c2hhMjU2OjQyOjhjOWJiYzJhNGEzOTVjNGE4Nzc2ZjE1ZDAyYjAxOTU1MjA2ZTgyYTA4NzY3OGM5ZTRhNDc2ODczZTBiNDNjNzE=');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');

		// No JS errors can be checked as there is no browser page involved
		$this->runErrorChecks = false;
	}
}
