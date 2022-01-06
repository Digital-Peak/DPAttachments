<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Installer\InstallerScript;

class Com_DPAttachmentsInstallerScript extends InstallerScript
{
	protected $minimumPhp      = '7.4.0';
	protected $minimumJoomla   = '4.0.0';
	protected $allowDowngrades = true;

	public function postflight($type, $parent)
	{
		if ($parent->getElement() != 'com_dpattachments') {
			return;
		}

		if ($type != 'install' && $type != 'discover_install') {
			return;
		}

		$content = 'deny from all
<Files ~ "\.(?i:gif|jpe?g|png|pdf)$">
order deny,allow
allow from all
</Files>';

		$folder = JPATH_ROOT . '/media/com_dpattachments/attachments/';
		mkdir($folder);
		file_put_contents($folder . '.htaccess', $content);
	}
}
