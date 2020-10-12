<?php
/**
 * @package    DPAttachments
 * @copyright  (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

?>
<div class="com-dpattachments-attachment com-dpattachments-attachment-png">
	<h3 class="com-dpattachments-attachment__header"><?php echo $this->escape($this->item->title); ?></h3>
	<?php require_once 'image.php'; ?>
</div>
