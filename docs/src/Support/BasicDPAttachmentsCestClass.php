<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Docs\Support;

class BasicDPAttachmentsCestClass
{
	public function _failed(AcceptanceTester $I)
	{
		$I->pause();
	}
}
