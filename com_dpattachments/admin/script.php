<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;

class Com_DPAttachmentsInstallerScript extends InstallerScript
{
	protected $minimumPhp      = '8.1.0';
	protected $minimumJoomla   = '4.4.0';
	protected $allowDowngrades = true;

	public function postflight(string $type, InstallerAdapter $parent): void
	{
		if ($parent->getElement() != 'com_dpattachments') {
			return;
		}

		if ($type !== 'install' && $type !== 'discover_install') {
			return;
		}

		$content = 'deny from all
<Files ~ "\.(?i:gif|jpe?g|png|pdf)$">
order deny,allow
allow from all
</Files>';

		$folder = JPATH_ROOT . '/media/com_dpattachments/attachments/';
		if (!is_dir($folder)) {
			mkdir($folder);
		}

		file_put_contents($folder . '.htaccess', $content);
	}
}
