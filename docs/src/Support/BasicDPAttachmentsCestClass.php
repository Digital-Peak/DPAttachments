<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Docs\Support;

class BasicDPAttachmentsCestClass
{
	public function _failed(AcceptanceTester $I)
	{
		$I->pause();
	}
}
