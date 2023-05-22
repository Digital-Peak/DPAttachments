<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2018 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

defined('_JEXEC') or die();

echo trim(preg_replace("/\([^)]+\)/", "", $this->file->getOriginalFilename()));
