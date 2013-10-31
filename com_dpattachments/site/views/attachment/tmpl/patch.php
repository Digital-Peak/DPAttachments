<?php
/**
 * @package		DPAttachments
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$input = JFactory::getApplication()->input;

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_dpattachments/views/attachment/tmpl/patch.css');

$content = JFile::read(DPAttachmentsCore::getPath($this->item->path, $this->item->context));

if ($input->get('tmpl') != 'component') {
    ?>
<div class="page-header">
	<h2><?php echo $this->escape($this->item->title); ?></h2>
</div>
<?php
}

$firstLine = true;
$patches = explode("index ", $content);
foreach ( $patches as $patch ) {
    if (strpos($patch, '### ') === 0) {
        continue;
    }

    $fromNumber = 0;
    $fromNumberStart = 0;
    $fromLength = 0;
    $toNumber = 0;
    $toNumberStart = 0;
    $toLength = 0;
    $content = false;
    foreach ( explode("\n", $patch) as $line ) {
        if ($toNumberStart + $toLength < $toNumber && $fromNumberStart + $fromLength < $fromNumber) {
            break;
        }
        if ($line == '\ No newline at end of file') {
            continue;
        }
        if (strpos($line, '@@ ') === 0) {
            $stripped = explode(' ', substr($line, 3, strlen($line) - 6));

            $tmp = explode(',', $stripped[0]);
            $fromNumber = str_replace(array('+', '-'), '', $tmp[0]);
            $fromNumberStart = $fromNumber;
            if (count($tmp) > 1) {
                $fromLength = $tmp[1];
            }

            $tmp = explode(',', $stripped[1]);
            $toNumber = str_replace(array('+', '-'), '', $tmp[0]);
            $toNumberStart = $toNumberStart;
            if (count($tmp) > 1) {
                $toLength = $tmp[1];
            }
            $content = true;
            continue;
        }

        if (strpos($line, '--- ') === 0) {
            echo '<h4>' . substr($line, 4) . '</h4>' . PHP_EOL;
            echo '<table class="table table-condensed">';
            continue;
        }

        if (! $content) {
            continue;
        }

        $added = null;
        if (strpos($line, '-') === 0) {
            $added = false;
        } else if (strpos($line, '+') === 0) {
            $added = true;
        }

        echo '<tr class="' . ($added ? 'dp-added' : ($added === false ? 'dp-removed' : 'dp-neutral')) . '">';
        $prefix = '&nbsp;&nbsp;';
        if ($added === false) {
            echo '<td>' . $fromNumber . '</td><td></td>';
            $count = 1;
            $line = str_replace('-', '', $line, $count);
            $prefix = '-&nbsp;';
            $fromNumber ++;
        } else if ($added === true) {
            echo '<td></td><td>' . $toNumber . '</td>';
            $count = 1;
            $line = str_replace('+', '', $line, $count);
            $prefix = '+&nbsp;';
            $toNumber ++;
        } else {
            echo '<td>' . $fromNumber . '</td><td>' . $toNumber . '</td>';
            $toNumber ++;
            $fromNumber ++;
        }

        echo '<td class="dp-content"><span>' . $prefix . '</span>' . htmlentities($line) . '</td>';
        echo '</tr>' . PHP_EOL;
    }
    echo '</table>';
}