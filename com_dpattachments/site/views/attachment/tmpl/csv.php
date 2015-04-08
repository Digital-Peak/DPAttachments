<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2015 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$input = JFactory::getApplication()->input;

JLoader::import('components.com_dpattachments.libraries.csv-parser.parser', JPATH_ADMINISTRATOR);

$content = JFile::read(DPAttachmentsCore::getPath($this->item->path, $this->item->context));

if ($input->get('tmpl') != 'component')
{
	?>
<div class="page-header">
	<h2><?php echo $this->escape($this->item->title); ?></h2>
</div>
<?php
}

$delimiter = ',';
$delimiterCount = 0;
foreach ( array(',', ';', "\t") as $char )
{
	$tmp = substr_count($content, $char);
	if ($tmp > $delimiterCount)
	{
		$delimiter = $char;
		$delimiterCount = $tmp;
	}
}
$csv = new parseCSV();
$csv->encoding('UTF-16', 'UTF-8');
$csv->delimiter = $delimiter;
$csv->parse($content);
?>
<table class="table">
<tr>
<?php foreach ( $csv->titles as $title )
{?>
	<td><?php echo $title;?></td>
<?php
}?>
</tr>
<?php foreach ( $csv->data as $row )
{?>
<tr>
	<?php foreach ($row as $value)
	{?>
	<td><?php echo nl2br(htmlentities($value));?></td>
	<?php
	}?>
</tr>
<?php
}?>
</table>
