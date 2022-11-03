<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;

class Pkg_DPAttachmentsInstallerScript extends InstallerScript
{
	protected $minimumPhp    = '7.4.0';
	protected $minimumJoomla = '4.0.0';

	public function postflight($type, $parent)
	{
		if ($parent->getElement() != 'pkg_dpattachments') {
			return;
		}

		if ($type != 'install' && $type != 'discover_install') {
			return;
		}

		$db = Factory::getDBO();
		$db->setQuery("update #__extensions set enabled=1 where type = 'plugin' and element = 'dpattachments'");
		$db->execute();
	}
}
