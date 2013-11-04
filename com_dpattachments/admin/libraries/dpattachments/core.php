<?php
/**
 * @package		DPAttachments
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

/**
 * Public DPAttachments API class.
 *
 * This class can be used to enable attachment support in your
 * Joomla extension.
 *
 * To include attachment support use the following code in your
 * view (default.php) file:
 *
 * JLoader::import('components.com_dpattachments.libraries.dpattachments.core', JPATH_ADMINISTRATOR);
 * if (class_exists('DPAttachmentsCore')) {
 * echo DPAttachmentsCore::render('com_foo.bar', $item->id);
 * }
 */
class DPAttachmentsCore {

	/**
	 * The cached items.
	 *
	 * @var array
	 */
	private static $itemCache = array();

	/**
	 * The render function which takes care to render the HTML
	 * code.
	 * A HTML string is returned which can be printed in any
	 * Joomla view.
	 *
	 * @param string $context
	 * @param string $itemId
	 * @return string
	 */
	public static function render($context, $itemId) {
		if (! self::isEnabled()) {
			return '';
		}
		JFactory::getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

		$path = JComponentHelper::getParams('com_dpattachments')->get('attachment_path', 'media/com_dpattachments/attachments/');
		$path = trim($path, '/') . '/' . $context;

		$buffer = '<h4>' . JText::_('COM_DPATTACHMENTS_ATTACHMENTS') . '</h4>';

		$attachments = self::getAttachments($context, $itemId);
		$count = count($attachments);

		$buffer .= '<div id="dpattachments-container">';
		for($i = 0; $i < $count; $i ++) {
			$attachment = $attachments[$i];
			if ($i % 2 == 0) {
				$buffer .= '<div class="row-fluid">';
			}
			$buffer .= '<div class="span6">';
			$buffer .= self::toHtml($attachment);
			$buffer .= '</div>';
			if ($i % 2 == 1) {
				$buffer .= '</div>';
			}
		}
		if ($count && ($count - 1) % 2 != 1) {
			$buffer .= '</div>';
		}
		$buffer .= '</div>';

		$doc = JFactory::getDocument();
		if ($count) {
			JHtmlBootstrap::framework();
			$buffer .= "<div id='dpattachments-modal' class='modal fade hide' tabindex='-1' role='dialog' aria-hidden='true'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
                    <h3></h3>
                </div>
                <iframe id='dpattachments-iframe' style='zoom:0.60;width:99.6%;height:500px;border:none'></iframe>
                <div class='modal-footer'><button class='btn btn-primary' data-dismiss='modal' aria-hidden='true'>" .
					 JText::_('COM_DPATTACHMENTS_CLOSE') . "</button></div>
              </div>";

			$script = "
            jQuery(document).on('click', '.dpattachments-button', function (event) {
                event.preventDefault();
                jQuery('#dpattachments-iframe').attr('src', this.href);
                jQuery('#dpattachments-modal h3').html(jQuery(this).attr('title')+\" <a href='\"+this.href.replace('tmpl=component', '')+\"' title='" .
					 JText::_('COM_DPATTACHMENTS_TEXT_FULL_SCREEN_MODE') . "'><span class='icon-expand small'></span></a>\");
                jQuery('#dpattachments-modal').modal();
            });";
			$doc->addScriptDeclaration('jQuery(document).ready(function(){' . $script . '});');
		}

		if (! self::canDo('core.edit', $context, $itemId)) {
			return $buffer;
		}

		$buffer .= '<form action="' . JRoute::_('index.php?option=com_dpattachments&task=attachment.upload') .
				 '" method="get" class="alert alert-info" id="dpattachments-fileupload">';
		$buffer .= '<span class="clearfix">' . JText::_('COM_DPATTACHMENTS_TEXT_SELECT_FILE') . ' ';
		$buffer .= '<span id="dpattachments-text-drag">' . JText::_('COM_DPATTACHMENTS_TEXT_DROP') . '</span> ';
		$buffer .= JText::_('COM_DPATTACHMENTS_TEXT_TO_UPLOAD') . '</span> ';
		$buffer .= '<input type="file" name="file">';
		$buffer .= '<p id="dpattachments-text-paste">' . JText::_('COM_DPATTACHMENTS_TEXT_PASTE') . '</p> ';
		$buffer .= '<input type="hidden" name="attachment[context]" value="' . $context . '" />';
		$buffer .= '<input type="hidden" name="attachment[item_id]" value="' . $itemId . '" />';
		$buffer .= JHtml::_('form.token');
		$buffer .= '<div class="progress progress-striped progress-success active hide" id="dpattachments-progress"><div class="bar" style="text-align:left"></div></div>';
		$buffer .= '</form>';

		JHtml::_('jquery.framework');
		$doc->addScript(JUri::root() . '/components/com_dpattachments/libraries/uploader/filereader.min.js');

		$doc->addScriptDeclaration("jQuery(document).ready(function(){
	var opts = {
		dragClass: 'alert-success',
		readAsMap: {},
		on: {
			groupstart: function(group) {
				for (var i = 0; i < group.files.length; i++) {
					var file = group.files[i];

					var fd = new FormData(jQuery('#dpattachments-fileupload')[0]);
				    fd.append('file', file);

        	        jQuery('#dpattachments-progress').show();
        	        jQuery('#dpattachments-progress div').html('<p>0%</p>');
					jQuery.ajax({
					    url: jQuery('#dpattachments-fileupload').attr('action'),
					    data: fd,
					    processData: false,
					    contentType: false,
	                    type: 'POST',
                        xhr: function(){
                            var myXHR = jQuery.ajaxSettings.xhr();
                            myXHR.upload.addEventListener('progress', function (e) {
			        	        if (e.lengthComputable) {
			            	        var percentage = Math.round((e.loaded * 100) / e.total);
			            	        jQuery('#dpattachments-progress div').html('<p>'+percentage+'%</p>');
			            	        jQuery('#dpattachments-progress div').css('width', percentage + '%');
			        	        }
					        }, false);
                            return myXHR;
                        },
	                    success: function(responseText){
	                       var json = jQuery.parseJSON(responseText);
					       Joomla.renderMessages(json.messages);

                	       jQuery('#dpattachments-progress').fadeOut();
                           jQuery('#dpattachments-container').append(json.data.html);
	                    }
					});
				}
			}
		}
	};

	jQuery('#dpattachments-fileupload, #dpattachments-fileupload input[type=file]').fileReaderJS(opts);
	jQuery('body').fileClipboard(opts);
    if (!FileReaderJS.enabled) {
        jQuery('#dpattachments-text-paste').hide();
        jQuery('#dpattachments-text-drag').hide();
    }
    if (typeof jQuery('body')[0]['onpaste'] != 'object') {
        jQuery('#dpattachments-text-paste').hide();
    }
    jQuery(':file').filestyle({buttonText: '" .
				 JText::_('COM_DPATTACHMENTS_BUTTON_SELECT_FILE') . "', classButton: 'btn btn-small', input: false});
});");

		return $buffer;
	}

	/**
	 * Deletes the attachment for the given context and
	 * item ID.
	 * Returns true on success, false otherwise.
	 *
	 * @param string $context
	 * @param string $itemId
	 * @return boolean
	 */
	public static function delete($context, $itemId) {
		$ids = array();
		foreach ( self::getAttachments($context, $itemId) as $attachment ) {
			JFile::delete(self::getPath($attachment->path, $attachment->context));
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

	/**
	 * Check if the given action can be performed for the item in
	 * the given context.
	 * The context will be splitted and a table is tried to be loaded
	 * for the given context with the data of the item ID.
	 * For example for the context com_dpcalendar.event a table with
	 * the name DPCalendarTableEvent will be loaded. On a second step
	 * the loaded table instance will be checked if it has an asset_id
	 * or catid field to check permissions against them. If this is not
	 * the case a fallback will be done to the DPAttachments options permission
	 * configuration.
	 *
	 * @param string $action
	 * @param string $context
	 * @param string $itemId
	 * @return boolean
	 */
	public static function canDo($action, $context, $itemId) {
		$key = $context . '.' . $itemId;

		list($component, $modelName) = explode('.', $context);
		if (! key_exists($key, self::$itemCache)) {
			// load the model to get the item

			$tableName = ucfirst($modelName);
			$prefix = ucfirst(str_replace('com_', '', $component)) . 'Table';

			// Handle the content table special
			if ($tableName == 'Article') {
				$prefix = 'JTable';
				$tableName = 'Content';
			}

			// Handle the category table special
			if ($tableName == 'Category') {
				$prefix = 'JTable';
				$tableName = 'Category';
			}

			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $component . '/tables');
			$table = JTable::getInstance($tableName, $prefix);

			if ($table) {
				$table->load($itemId);
			}

			self::$itemCache[$key] = $table;
		}

		$user = JFactory::getUser();

		$item = self::$itemCache[$key];

		// no item so we can only check for component permission
		if (! $item || ! $item->id) {
			return $user->authorise($action, $component) || $user->authorise($action, 'com_dpattachments');
		}

		$asset = $component;
		if (isset($item->asset_id)) {
			$asset = $item->asset_id;
		} else if (isset($item->catid)) {
			$asset = $component . '.category.' . $item->catid;
		}

		// check direct permission
		if ($user->authorise($action, $asset)) {
			return true;
		}

		// if the edit action is requestd we check for edit.own
		if ($action == 'core.edit' && isset($item->created_by)) {
			if ($user->authorise('core.edit.own', $asset) && $item->created_by == $user->id) {
				return true;
			}
		}

		// the creator will always have the edit state permissions to trsah attachments
		if ($action == 'core.edit.state' && isset($item->created_by)) {
			if ($item->created_by == $user->id) {
				return true;
			}
		}

		// fallback to the DPAttachments permissions
		return $user->authorise($action, 'com_dpattachments');
	}

	/**
	 * Returns a local file system pathe for the given filename and
	 * context.
	 *
	 * @param string $attachmentPath
	 * @param string $context
	 * @return string
	 */
	public static function getPath($attachmentPath, $context) {
		$folder = JComponentHelper::getParams('com_dpattachments')->get('attachment_path', 'media/com_dpattachments/attachments/');
		$folder = trim($folder, '/');
		return JPATH_ROOT . '/' . $folder . '/' . $context . '/' . $attachmentPath;
	}

	public static function toHtml($attachment) {
		if (empty($attachment)) {
			return '';
		}
		$canEditState = self::canDo('core.edit.state', $attachment->context, $attachment->item_id);
		$canEdit = self::canDo('core.edit', $attachment->context, $attachment->item_id);

		$buffer = '';
		if (self::previewAvailable($attachment)) {
			$buffer .= '<a href="' . JRoute::_('index.php?option=com_dpattachments&view=attachment&tmpl=component&id=' . (int)$attachment->id) .
					 '" class="dpattachments-button" title="' . $attachment->title . '">' . $attachment->title . '</a>';
		} else {
			$buffer .= $attachment->title;
		}

		$author = $attachment->author_name;
		if ($attachment->created_by_alias) {
			$author = $attachment->created_by_alias;
		}
		$buffer .= ' <span class="small">[' . self::size($attachment->size) . ']</span> ';
		$buffer .= '<a href="' . JRoute::_('index.php?option=com_dpattachments&task=attachment.download&id=' . (int)$attachment->id) .
				 '" target="_blank"><span class="icon-download"></span></a>';
		$buffer .= '<p>' . JText::sprintf('COM_DPATTACHMENTS_TEXT_UPLOADED_LABEL', JHtmlDate::relative($attachment->created), $author) . '</p>';

		if ($canEdit || $canEditState) {
			$buffer .= '<div class="btn-toolbar"><div class="btn-group">';
			if ($canEdit) {
				$buffer .= '<a href="' .
						 JRoute::_('index.php?option=com_dpattachments&task=attachment.edit&a_id=' . $attachment->id . '&return=' .
						 base64_encode(JUri::getInstance()->toString())) . '" class="btn btn-small">';
				$buffer .= '    <span class="icon-edit"></span> ' . JText::_('JACTION_EDIT');
				$buffer .= '</a>';
			}
			if ($canEditState) {
				$buffer .= '<a href="' .
						 JRoute::_('index.php?option=com_dpattachments&task=attachment.publish&state=-2&id=' . $attachment->id . '&' . JSession::getFormToken() .
						 '=1&return=' . base64_encode(JUri::getInstance()->toString())) . '" class="btn btn-small">';
				$buffer .= '    <span class="icon-trash"></span> ' . JText::_('JTRASH');
				$buffer .= '</a>';
			}
			$buffer .= '</div></div>';
		}

		return $buffer;
	}

	/**
	 * Internal helper function to check if a preview is available
	 * for the given attachment.
	 *
	 * @return boolean
	 */
	private static function previewAvailable($attachment) {
		JLoader::import('joomla.filesystem.folder');

		$ext = strtolower(JFile::getExt($attachment->path));
		foreach ( JFolder::files(JPATH_SITE . '/components/com_dpattachments/views/attachment/tmpl') as $file ) {
			if (JFile::stripExt($file) == $ext) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Internal helper function to check if the acual menu item or component
	 * is enabled for attachment support.
	 *
	 * @return boolean
	 */
	private static function isEnabled() {
		$input = JFactory::getApplication()->input;
		$params = JComponentHelper::getParams('com_dpattachments');

		// check for menu items to include
		$menuItems = $params->get('menuitems');
		if (! empty($menuItems)) {
			if (! is_array($menuItems)) {
				$menuItems = array($menuItems);
			}

			if (! in_array($input->getInt('Itemid'), $menuItems)) {
				return false;
			}
		}

		$menuItems = $params->get('menuitems_exclude');
		if (! empty($menuItems)) {
			if (! is_array($menuItems)) {
				$menuItems = array($menuItems);
			}

			if (in_array($input->getInt('Itemid'), $menuItems)) {
				return false;
			}
		}

		// check for components to include
		$components = $params->get('components');
		if (! empty($components)) {
			if (! is_array($components)) {
				$components = array($components);
			}

			if (! in_array($input->getCmd('option'), $components)) {
				return false;
			}
		}

		$components = $params->get('components_exclude');
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

	/**
	 * Internal helper function to get the attachments for the given
	 * context and item ID.
	 *
	 * @param string $context
	 * @param string $itemId
	 * @return array
	 */
	private static function getAttachments($context, $itemId) {
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpattachments/models', 'DPAttachmentsModel');
		$model = JModelLegacy::getInstance('Attachments', 'DPAttachmentsModel');
		$model->getState();
		$model->setState('filter.item', $itemId);
		$model->setState('filter.context', $context);
		$model->setState('list.limit', 1000);
		$model->setState('list.start', 0);

		return $model->getItems();
	}

	/**
	 * Internal helper function to create a human readable
	 * size string for the given size which is in bytes.
	 *
	 * @param integer $size
	 * @return string
	 */
	private static function size($size) {
		// size in bytes
		if ($size <= 1024) {
			return $size . JText::_('COM_DPATTACHMENTS_BYTE_SHORT');
		}

		// size in kilo bytes
		$filekb = $size / 1024;
		if ($filekb <= 1024) {
			$flieinkb = round($filekb, 2);
			return $flieinkb . JText::_('COM_DPATTACHMENTS_KILOBYTE_SHORT');
		}

		// size in mega bytes
		$filemb = $filekb / 1024;
		$fileinmb = round($filemb, 2);
		return $fileinmb . JText::_('COM_DPATTACHMENTS_MEGA_BYTE_SHORT');
	}
}