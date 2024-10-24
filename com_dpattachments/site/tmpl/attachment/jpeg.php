<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();
?>
<div class="com-dpattachments-attachment com-dpattachments-attachment-jpeg">
	<h3 class="com-dpattachments-attachment__header"><?php echo $this->escape($this->item->title); ?></h3>
	<?php require_once __DIR__ . '/image.php'; ?>
</div>
