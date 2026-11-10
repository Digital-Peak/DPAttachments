<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2025 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die;

use DigitalPeak\Plugin\Installer\DPAttachments\Extension\DPAttachments;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
	public function register(Container $container): void
	{
		$container->set(
			PluginInterface::class,
			static function (Container $container): DPAttachments {
				$plugin = new DPAttachments(
					(array)PluginHelper::getPlugin('installer', 'dpattachments')
				);
				$plugin->setApplication(Factory::getApplication());
				$plugin->setDatabase($container->get(DatabaseInterface::class));

				return $plugin;
			}
		);
	}
};
