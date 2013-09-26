<?php
/**
 * @package		DPAttachments
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPAttachmentsPlugin extends JPlugin {

    public function onContentAfterDisplay($context, $item, $params) {
        if (! $this->isEnabled($context)) {
            return '';
        }
        return $this->render($context, $item);
    }

    public function onContentAfterDelete($context, $item) {
        return $this->delete($context, $item);
    }

    protected function render($context, $item) {
        JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

        $attachments = $this->getAttachments($context, $item);

        $user = JFactory::getUser();
        $count = count($attachments);
        $path = JComponentHelper::getParams('com_dpattachments')->get('attachment_path', 'media/com_dpattachments/attachments/' . $context);

        $buffer = '<h4>' . JText::_('COM_DPATTACHMENTS_ATTACHMENTS') . '</h4>';

        $canEditState = DPAttachmentsHelper::canDo('core.edit.state', $context, (int)$item->id);
        $canEdit = DPAttachmentsHelper::canDo('core.edit', $context, (int)$item->id);
        $canEditOwn = DPAttachmentsHelper::canDo('core.edit.own', $context, (int)$item->id);
        for($i = 0; $i < $count; $i ++) {
            $attachment = $attachments[$i];
            if ($i % 2 == 0) {
                $buffer .= '<div class="row-fluid">';
            }
            $buffer .= '<div class="span6">';
            $isImage = in_array(JFile::getExt($attachment->path), array('png', 'gif', 'jpg', 'jpeg'));
            if ($isImage) {
                $buffer .= '<a href="' . $path . '/' . $attachment->path . '" class="dpattachment-button" title="' . $attachment->title . '">' . $attachment->title .
                         '</a>';
            } else {
                $buffer .= $attachment->title;
            }

            $author = $attachment->author_name;
            if ($attachment->created_by_alias) {
                $author = $attachment->created_by_alias;
            }
            $buffer .= ' <span class="small">[' . DPAttachmentsHelper::size($attachment->size) . ']</span> ';
            $buffer .= '<a href="' . JRoute::_('index.php?option=com_dpattachments&task=attachment.download&id=' . (int)$attachment->id) .
                     '" target="_blank"><span class="icon-download"></span></a>';
            $buffer .= '<p>' . JText::sprintf('COM_DPATTACHMENTS_VIEW_PLUGIN_UPLOADED_LABEL', JHtmlDate::relative($attachment->created), $author) . '</p>';

            if ($canEdit || $canEditState) {
                $buffer .= '<div class="btn-toolbar"><div class="btn-group">';
                if ($canEdit) {
                    $buffer .= '<a href="' . JRoute::_('index.php?option=com_dpattachments&task=attachment.edit&a_id=' . $attachment->id . '&return=' .
                             base64_encode(JUri::getInstance()->toString())) . '" class="btn btn-small">';
                    $buffer .= '    <span class="icon-edit"></span> ' . JText::_('JACTION_EDIT');
                    $buffer .= '</a>';
                }
                if ($canEditState) {
                    $buffer .= '<a href="' . JRoute::_('index.php?option=com_dpattachments&task=attachment.publish&state=-2&id=' . $attachment->id . '&' .
                             JSession::getFormToken() . '=1&return=' . base64_encode(JUri::getInstance()->toString())) . '" class="btn btn-small">';
                    $buffer .= '    <span class="icon-trash"></span> ' . JText::_('JTRASH');
                    $buffer .= '</a>';
                }
                $buffer .= '</div></div>';
            }

            $buffer .= '</div>';
            if ($i % 2 == 1) {
                $buffer .= '</div>';
            }
        }
        if ($count && ($count - 1) % 2 != 1) {
            $buffer .= '</div>';
        }

        $doc = JFactory::getDocument();
        if ($count) {
            JHtmlBootstrap::framework();
            $buffer .= "<div id='dpattachments-modal' class='modal fade hide' tabindex='-1' role='dialog' aria-hidden='true'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>x</button>
                    <h3></h3>
                </div>
                <div class='modal-body'><img id='dpattachments-image' src=''></div>
                <div class='modal-footer'><button class='btn btn-primary' data-dismiss='modal' aria-hidden='true'>" .
                     JText::_('COM_DPATTACHMENTS_CLOSE') . "</button></div>
              </div>";

            $script = "
            jQuery('.dpattachment-button').click(function (event) {
                event.preventDefault();
                jQuery('#dpattachments-image').attr('src', this.href);
                jQuery('#dpattachments-modal h3').html(jQuery(this).attr('title'));
                jQuery('#dpattachments-modal').modal();
            });";
            $doc->addScriptDeclaration('jQuery(document).ready(function(){' . $script . '});');
        }

        if (! $canEdit) {
            return $buffer;
        }

        $buffer .= '<form action="' . JRoute::_('index.php?option=com_dpattachments&task=attachment.upload') .
                 '" method="get" class="dropzone alert alert-info" id="dpattachmentfileupload">';
        $buffer .= '<input type="hidden" name="attachment[context]" value="' . $context . '" />';
        $buffer .= '<input type="hidden" name="attachment[item_id]" value="' . (int)$item->id . '" />';
        $buffer .= JHtml::_('form.token');
        $buffer .= '</form>';

        $doc->addScript(JUri::root() . '/components/com_dpattachments/libraries/dropzone/dropzone.min.js');

        $doc->addScriptDeclaration("Dropzone.options.dpattachmentfileupload = {
			createImageThumbnails: false,
            dictDefaultMessage: '" . JText::_('COM_DPATTACHMENTS_VIEW_PLUGIN_TEXT_DROP') . "',
            dictFallbackMessage: '" . JText::_('COM_DPATTACHMENTS_VIEW_PLUGIN_TEXT_FALLBACK') . "',
            dictFallbackText: '" . JText::_('COM_DPATTACHMENTS_VIEW_PLUGIN_TEXT_FALLBACK_FORM') . "',
            previewTemplate: '<div class=\'dz-preview dz-file-preview\'><div class=\'dz-details\'><div class=\'dz-filename\'><span data-dz-name></span></div><div class=\'dz-size\' data-dz-size></div></div><div class=\'dz-progress\'><span class=\'dz-upload\' data-dz-uploadprogress></span></div><div class=\'dz-error-message\'><span data-dz-errormessage></span></div></div>',
		 	init: function() {
				this.on('success', function(file, responseText) {
					var json = jQuery.parseJSON(responseText);
		 			if (json.success == false) {
		 				this.removeFile(file);
		 			}
					Joomla.renderMessages(json.messages);
				});
		  	}
		};");

        return $buffer;
    }

    protected function delete($context, $item) {
        $ids = array();
        foreach ( $this->getAttachments($context, $item) as $attachment ) {
            JFile::delete(DPAttachmentsHelper::getPath($attachment->path, $attachment->context));
            $ids[] = (int)$attachment->id;
        }

        if (empty($ids)) {
            return true;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete('#__dpattachments');
        $query->where('id in (' . implode(',', $ids) . ')');
        $db->setQuery($query);

        $db->execute();

        return true;
    }

    private function isEnabled($context) {
        $input = JFactory::getApplication()->input;

        // check for menu items to include
        $menuItems = $this->params->get('menuitems');
        if (! empty($menuItems)) {
            if (! is_array($menuItems)) {
                $menuItems = array($menuItems);
            }

            if (! in_array($input->getInt('Itemid'), $menuItems)) {
                return false;
            }
        }

        $menuItems = $this->params->get('menuitems_exclude');
        if (! empty($menuItems)) {
            if (! is_array($menuItems)) {
                $menuItems = array($menuItems);
            }

            if (in_array($input->getInt('Itemid'), $menuItems)) {
                return false;
            }
        }

        // check for components to include
        $components = $this->params->get('components');
        if (! empty($components)) {
            if (! is_array($components)) {
                $components = array($components);
            }

            if (! in_array($input->getCmd('option'), $components)) {
                return false;
            }
        }

        $components = $this->params->get('components_exclude');
        if (! empty($components)) {
            if (! is_array($components)) {
                $components = array($components);
            }

            if (in_array($input->getCmd('option'), $components)) {
                return false;
            }
        }

        return true;
    }

    private function getAttachments($context, $item) {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/models', 'DPAttachmentsModel');
        $model = JModelLegacy::getInstance('Attachments', 'DPAttachmentsModel');
        $model->getState();
        $model->setState('filter.item', (int)$item->id);
        $model->setState('filter.context', $context);
        $model->setState('list.limit', 1000);
        $model->setState('list.start', 0);

        return $model->getItems();
    }
}