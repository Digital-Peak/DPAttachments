<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2025 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Installer\DPAttachments\Extension;

\defined('_JEXEC') or die();

use Joomla\CMS\Event\Installer\BeforeUpdateSiteDownloadEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

class DPAttachments extends CMSPlugin
{
	use DatabaseAwareTrait;

	public function onInstallerBeforeUpdateSiteDownload(BeforeUpdateSiteDownloadEvent $event): void
	{
		$url = $event->getUrl();
		if (!str_contains($url, 'digital-peak.com')) {
			return;
		}

		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$query->select('ext.manifest_cache, u.name')->from('#__update_sites u');
		$query->where('u.location = :location')->bind(':location', $url);
		$query->join('right', '#__update_sites_extensions AS usext ON usext.update_site_id = u.update_site_id');
		$query->join('right', '#__extensions AS ext ON ext.extension_id = usext.extension_id');

		$db->setQuery($query);
		$row = $db->loadObject();
		if (!str_contains((string)$row->name, 'DPAttachments')) {
			return;
		}

		// Set versions, so we get a compact update XML back with only versions greater than whats installed
		// to prevent timeouts and other network issues
		$uri = Uri::getInstance($url);
		$uri->setVar('j', JVERSION);
		$uri->setVar('p', phpversion());
		$uri->setVar('m', $db->getVersion());

		$manifestData = new Registry($row->manifest_cache ?? '');
		if ($version = $manifestData->get('version')) {
			$uri->setVar('v', $version);
		}

		if ($uri->getVar('v') === 'DP_DEPLOY_VERSION') {
			return;
		}

		$event->updateUrl($uri->toString());
	}
}
