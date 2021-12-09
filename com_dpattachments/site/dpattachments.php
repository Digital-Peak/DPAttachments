<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

Factory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');
JLoader::import('components.com_dpattachments.vendor.autoload', JPATH_ADMINISTRATOR);

$controller = BaseController::getInstance('DPAttachments');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
