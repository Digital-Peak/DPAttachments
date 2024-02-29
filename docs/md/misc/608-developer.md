### Introduction
DPAttachments can be integrated easily into your existing Joomla extension, doesn't matter if it is a component, module or plugin. Just write the following line in a view file and DPAttachments does the rest. The `$app` is sometimes not available, then you can use `Joomla\CMS\Factory::getApplication`.

`echo $app->bootComponent('dpattachments')->render('com_demo.item', $object->id);`

### Write your own plugin
If you are not the developer of a Joomla extension but you want to integrate DPAttachments into it then you can write your own plugin. The only requirement is that the extensions fires an event where the plugin can be an observer of. Have a look at the [content plugin of DPAttachments](https://github.com/Digital-Peak/DPAttachments/tree/main/plg_content_dpattachments) as example, it integrates into the common content events. Basically you need the following code when there is an component com_awesomeimages with an entity image:

```
class AwesomeImages extends CMSPlugin
{
	public function onImageAfterDisplay($image)
	{
		return $this->getApplication()->bootComponent('dpattachments')->render('com_awesomeimages.image', $image->id);
	}
}
```

### Hook into DPAttachments
DPAttachments itself is extendable, there are the following plugin events where a plugin developer can listen to and interact accordingly. These are Joomla 4 events, so there is only one argument, a `Joomla\Event\Event` object which contains different arguments. Some of them do use the arguments for further processing, so the functions which are listening to the events should not return anything.

#### onDPAttachmentsBeforeProcessList
**Description**  
Before the list of attachments is processed.

**Arguments**
1. context  
The context of the list.
2. item_id  
The ID of the item of the list.
3. attachments  
The list of attachments, modify the list in this event as these attachments are used for further actions.
4. component  
The component which executes the action, so no booting is needed.
5. options  
The options which does contain different values, like the item or column count.

#### onDPAttachmentsAfterProcessList
**Description**  
After the list of attachments is processed.

**Arguments**
1. context  
The context of the list.
2. item_id  
The ID of the item of the list.
3. attachments  
The list of attachments, modify the list in this event as these attachments are used for further actions.
4. component  
The component which executes the action, so no booting is needed.
5. options  
The options which does contain different values, like the item or column count.

#### onDPAttachmentsBeforeRenderLayout
**Description**  
Before a layout is rendered in the DPAttachments context.

**Arguments**
1. name  
The name of the layout file.
2. data  
The data for the layout file.
3. component  
The component which executes the action, so no booting is needed.

#### onDPAttachmentsAfterRenderLayout
**Description**  
After a layout is rendered in the DPAttachments context.

**Arguments**
1. name  
The name of the layout file.
2. data  
The data for the layout file.
3. content  
The content of the layout file. Modify the content as it is used for rendering.
4. component  
The component which executes the action, so no booting is needed.

#### onDPAttachmentsCheckPermission
**Description**  
When a permission is checked. After the event is dispatched and it contains an argument `allowed` which is true, then it dows not further processing to check the permissions of an item.

**Arguments**
1. action  
The name of the action.
2. context  
The context of the check.
3. item_id  
The item to check the permissions for.
