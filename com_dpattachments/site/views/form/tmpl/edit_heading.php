<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2019 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!$this->params->get('show_page_heading')) {
	return;
}
?>
<div class="com-dpattachments-form-edit__header">
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
</div>
