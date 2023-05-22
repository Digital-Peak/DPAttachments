<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2022 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace Tests\Support\Page\Administrator;

use Tests\Support\AcceptanceTester;

class MediaListPage extends AcceptanceTester
{
	public static $url = '/administrator/index.php?option=com_media&path=local-images:/test';
}
