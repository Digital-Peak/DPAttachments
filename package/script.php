<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Filesystem\Folder;

class Pkg_DPAttachmentsInstallerScript extends InstallerScript implements DatabaseAwareInterface
{
	use DatabaseAwareTrait;

	protected $minimumPhp = '8.1.0';

	protected $minimumJoomla = '4.4.0';

	public function update(InstallerAdapter $parent): void
	{
		$file = $parent->getParent()->getPath('source') . '/deleted.php';
		if (file_exists($file)) {
			require $file;
		}

		$path    = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_dpattachments.xml';
		$version = null;

		if (file_exists($path)) {
			$manifest = simplexml_load_file($path);
			if ($manifest instanceof SimpleXMLElement) {
				$version = (string)$manifest->version;
			}
		}

		if (\in_array($version, [null, '', '0', 'DP_DEPLOY_VERSION'], true)) {
			return;
		}

		if (version_compare($version, '5.4.0') === -1) {
			$folders = Folder::folders(JPATH_ROOT, 'dpattachments', true, true, ['api', 'cache', 'cli', 'images', 'layouts', 'libraries', 'media', 'templates', 'test']);
			foreach ($folders as $folder) {
				if (!is_dir($folder . '/language')) {
					continue;
				}

				foreach (Folder::files($folder . '/language', '.', true, true) as $file) {
					if (str_starts_with(basename((string)$file), basename(\dirname((string)$file)))) {
						unlink($file);
					}
				}
			}
		}

		if (version_compare($version, '5.7.6') === -1) {
			$db = $this->getDatabase();
			$db->setQuery("update #__extensions set enabled=1 where type = 'plugin' and element = 'dpattachments'");
			$db->execute();
		}
	}

	public function postflight(string $type, InstallerAdapter $parent): void
	{
		if ($parent->getElement() !== 'pkg_dpattachments') {
			return;
		}

		if ($type !== 'install' && $type !== 'discover_install') {
			return;
		}

		$db = $this->getDatabase();
		$db->setQuery("update #__extensions set enabled=1 where type = 'plugin' and element = 'dpattachments'");
		$db->execute();
	}
}
