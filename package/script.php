<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\PackageAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Filesystem\Folder;

class Pkg_DPAttachmentsInstallerScript extends InstallerScript
{
	protected $minimumPhp    = '7.4.0';
	protected $minimumJoomla = '4.0.0';

	public function update(PackageAdapter $parent)
	{
		$file = $parent->getParent()->getPath('source') . '/deleted.php';
		if (file_exists($file)) {
			require $file;
		}

		$path    = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_dpattachments.xml';
		$version = null;

		if (file_exists($path)) {
			$manifest = simplexml_load_file($path);
			$version  = (string)$manifest->version;
		}

		if (empty($version) || $version == 'DP_DEPLOY_VERSION') {
			return;
		}

		if (version_compare($version, '5.4.0') == -1) {
			$folders = Folder::folders(JPATH_ROOT, 'dpattachments', true, true, ['api', 'cache', 'cli', 'images', 'layouts', 'libraries', 'media', 'templates', 'test']);
			foreach ($folders as $folder) {
				if (!is_dir($folder . '/language')) {
					continue;
				}

				foreach (Folder::files($folder . '/language', '.', true, true) as $file) {
					if (strpos(basename($file), basename(dirname($file))) === 0) {
						unlink($file);
					}
				}
			}
		}
	}

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
