<?php
/**
 * @package   DPAttachments
 * @copyright Copyright (C) 2022 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

$config = require_once \dirname(__DIR__) . '/DPDocker/code/config/.php-cs-fixer.php';

$config->getFinder()->in(__DIR__);

return $config;
