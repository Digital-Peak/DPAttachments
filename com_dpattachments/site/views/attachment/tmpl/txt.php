<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

?>
<div class="com-dpattachments-attachment com-dpattachments-attachment-txt">
	<h3 class="com-dpattachments-attachment__header"><?php echo $this->escape($this->item->title); ?></h3>
	<div class="com-dpattachments-attachment__content">
		<?php echo nl2br(htmlentities(JFile::read(\DPAttachments\Helper\Core::getPath($this->item->path, $this->item->context)))); ?>
	</div>
</div>
