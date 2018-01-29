<?php
/**
 * @package    DPAttachments
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2012 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$attachment = $displayData['attachment'];
if (!$attachment)
{
	return;
}

JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

$canEditState = DPAttachmentsCore::canDo('core.edit.state', $attachment->context, $attachment->item_id);
$canEdit = DPAttachmentsCore::canDo('core.edit', $attachment->context, $attachment->item_id);

$canPreview = false;
JLoader::import('joomla.filesystem.folder');
$ext = strtolower(JFile::getExt($attachment->path));
foreach (JFolder::files(JPATH_SITE . '/components/com_dpattachments/views/attachment/tmpl') as $file)
{
	if (JFile::stripExt($file) == $ext)
	{
		$canPreview = true;
		break;
	}
}

$buffer = '';
if ($canPreview)
{
	$buffer .= '<a href="' . JRoute::_('index.php?option=com_dpattachments&view=attachment&tmpl=component&id=' . (int)$attachment->id) .
			 '" class="dpattachments-button" title="' . $attachment->title . '">' . $attachment->title . '</a>';
}
else
{
	$buffer .= $attachment->title;
}

$author = isset($attachment->author_name) ? $attachment->author_name : $attachment->created_by;
if ($attachment->created_by_alias)
{
	$author = $attachment->created_by_alias;
}
$buffer .= ' <span class="small">[' . DPAttachmentsCore::size($attachment->size) . ']</span> ';
$buffer .= '<a href="' . JRoute::_('index.php?option=com_dpattachments&task=attachment.download&id=' . (int)$attachment->id) .
		 '" target="_blank"><span class="icon-download"></span></a>';
$buffer .= '<p>' . JText::sprintf('COM_DPATTACHMENTS_TEXT_UPLOADED_LABEL', JHtmlDate::relative($attachment->created), $author) . '</p>';

if ($canEdit || $canEditState)
{
	$buffer .= '<div class="btn-toolbar"><div class="btn-group">';
	if ($canEdit)
	{
		$buffer .= '<a href="' . JRoute::_(
				'index.php?option=com_dpattachments&task=attachment.edit&a_id=' . $attachment->id . '&return=' .
						 base64_encode(JUri::getInstance()->toString())) . '" class="btn btn-small">';
		$buffer .= '    <span class="icon-edit"></span> ' . JText::_('JACTION_EDIT');
		$buffer .= '</a>';
	}
	if ($canEditState)
	{
		$buffer .= '<a href="' . JRoute::_(
				'index.php?option=com_dpattachments&task=attachment.publish&state=-2&id=' . $attachment->id . '&' . JSession::getFormToken() .
						 '=1&return=' . base64_encode(JUri::getInstance()->toString())) . '" class="btn btn-small">';
		$buffer .= '    <span class="icon-trash"></span> ' . JText::_('JTRASH');
		$buffer .= '</a>';
	}
	$buffer .= '</div></div>';
}

echo $buffer;
