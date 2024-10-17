<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use ptlis\DiffParser\Line;
use ptlis\DiffParser\Parser;

HTMLHelper::_('stylesheet', 'com_dpattachments/dpattachments/views/attachment/patch.min.css', ['relative' => true]);

$parser  = new Parser();
$changes = $parser->parseFile(Factory::getApplication()->bootComponent('dpattachments')->getPath($this->item->path, $this->item->context));
?>
<div class="com-dpattachments-attachment com-dpattachments-attachment-patch">
	<h3 class="com-dpattachments-attachment__header"><?php echo $this->escape($this->item->title); ?></h3>
	<div class="com-dpattachments-attachment__content">
		<?php foreach ($changes->files as $file) { ?>
			<?php $this->file = $file; ?>
			<div class="dp-patch-file">
				<h4 class="dp-patch-file__name"><?php echo $this->loadtemplate('filename'); ?></h4>
				<?php foreach ($file->hunks as $hunk) { ?>
					<table class="dp-table dp-patch-hunk">
						<?php foreach ($hunk->lines as $line) { ?>
							<tr class="dp-patch-hunk__line dp-patch-hunk__line_<?php echo $line->operation; ?> dp-line">
								<td class="dp-line__original-nr"><?php echo $line->originalLineNo != -1 ? $line->originalLineNo : ''; ?></td>
								<td class="dp-line__new-nr"><?php echo $line->newLineNo != -1 ? $line->newLineNo : ''; ?></td>
								<td class="dp-line__operation">
									<?php echo $line->operation == Line::ADDED ? '+ ' : ''; ?>
									<?php echo $line->operation == Line::REMOVED ? '- ' : ''; ?>
								</td>
								<td class="dp-line__content"><?php echo htmlentities($line->content); ?></td>
							</tr>
						<?php } ?>
					</table>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</div>
