<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2019 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::_('stylesheet', 'com_dpattachments/views/form/edit.min.css', ['relative' => true]);

JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'com_dpattachments/views/form/edit.min.js', ['relative' => true], ['defer' => true]);
?>
<div class="com-dpattachments-form-edit<?php echo $this->pageclass_sfx ? ' com-dpattachments-form-edit-' . $this->pageclass_sfx : ''; ?>">
	<?php echo $this->loadtemplate('heading'); ?>
	<?php echo $this->loadtemplate('actions'); ?>
	<?php echo $this->loadtemplate('form'); ?>
</div>
