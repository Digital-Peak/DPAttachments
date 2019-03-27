<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2019 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::_('stylesheet', 'com_dpattachments/views/attachment/patch.min.css', ['relative' => true]);
$parser  = new \ptlis\DiffParser\Parser();
$changes = $parser->parseFile(\DPAttachments\Helper\Core::getPath($this->item->path, $this->item->context));
?>
<div class="com-dpattachments-attachment com-dpattachments-attachment-patch">
	<h3 class="com-dpattachments-attachment__header"><?php echo $this->escape($this->item->title); ?></h3>
	<div class="com-dpattachments-attachment__content">
		<?php foreach ($changes->getFiles() as $file) { ?>
			<?php $this->file = $file; ?>
			<div class="dp-patch-file">
				<h4 class="dp-patch-file__name"><?php echo $this->loadtemplate('filename'); ?></h4>
				<?php foreach ($file->getHunks() as $hunk) { ?>
					<table class="dp-table dp-patch-hunk">
						<?php foreach ($hunk->getLines() as $line) { ?>
							<tr class="dp-patch-hunk__line dp-patch-hunk__line_<?php echo $line->getOperation(); ?> dp-line">
								<td class="dp-line__original-nr"><?php echo $line->getOriginalLineNo() != -1 ? $line->getOriginalLineNo() : ''; ?></td>
								<td class="dp-line__new-nr"><?php echo $line->getNewLineNo() != -1 ? $line->getNewLineNo() : ''; ?></td>
								<td class="dp-line__operation">
									<?php echo $line->getOperation() == \ptlis\DiffParser\Line::ADDED ? '+ ' : ''; ?>
									<?php echo $line->getOperation() == \ptlis\DiffParser\Line::REMOVED ? '- ' : ''; ?>
								</td>
								<td class="dp-line__content"><?php echo htmlentities($line->getContent()); ?></td>
							</tr>
						<?php } ?>
					</table>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</div>
