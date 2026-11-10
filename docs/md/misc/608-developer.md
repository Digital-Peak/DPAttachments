### Introduction
DPAttachments can be integrated easily into your existing Joomla extension, doesn't matter if it is a component, module or plugin. Just write the following line in a view file and DPAttachments does the rest. The `$app` is sometimes not available, then you can use `Joomla\CMS\Factory::getApplication`.

`echo $app->bootComponent('dpattachments')->render('com_demo.item', $object->id);`

### Write your own plugin
If you are not the developer of a Joomla extension but you want to integrate DPAttachments into it then you can write your own plugin. The only requirement is that the extensions fires an event where the plugin can be an observer of. Have a look at the [content plugin of DPAttachments](https://github.com/Digital-Peak/DPAttachments/tree/main/plg_content_dpattachments) as example, it integrates into the common content events. Basically you need the following code when there is an component com_awesomeimages with an entity image:

```
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class AwesomeImages extends CMSPlugin implements SubscriberInterface
{
	public static function getSubscribedEvents(): array
	{
		return ['onImageAfterDisplay' => 'render'];
	}

	public function render(Event $event): void
	{
		$image = $event->getArgument('image');

		$event->setArgument(
			'result',
			array_merge(
				$event->getArgument('result', []),
				[$this->getApplication()->bootComponent('dpattachments')->render('com_awesomeimages.image', $image->id)]
			)
		);
	}
}
```

### Hook into DPAttachments
DPAttachments itself is extendable, there are the following plugin events where a plugin developer can listen to and interact accordingly. These are Joomla 5 events, so the listener gets only one argument, an event object of a dedicated class from the `DigitalPeak\Component\DPAttachments\Administrator\Event` namespace. It is recommended to implement the `Joomla\Event\SubscriberInterface` in your plugin. The event classes have typed getters for the arguments and `update*` functions for the ones which are used for further processing, the functions which are listening to the events should not return anything. The arguments can still be accessed with `$event->getArgument()` and `$event->setArgument()`.

The list and layout events are dispatched after the `content` plugins are imported, the permission event imports the `dpattachments` plugins. So put your plugin into one of these groups (or the `system` group).

```
use DigitalPeak\Component\DPAttachments\Administrator\Event\BeforeProcessListEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

class HidePdf extends CMSPlugin implements SubscriberInterface
{
	public static function getSubscribedEvents(): array
	{
		return ['onDPAttachmentsBeforeProcessList' => 'filter'];
	}

	public function filter(BeforeProcessListEvent $event): void
	{
		$event->updateAttachments(array_filter(
			$event->getAttachments(),
			static fn ($attachment): bool => !str_ends_with($attachment->path, '.pdf')
		));
	}
}
```

#### onDPAttachmentsBeforeProcessList
**Class**  
`BeforeProcessListEvent`, getters `getContext()`, `getItemId()`, `getAttachments()`, `getComponent()`, `getOptions()`, modify the attachments with `updateAttachments(array $attachments)`.

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
**Class**  
`AfterProcessListEvent`, same functions as `BeforeProcessListEvent`.

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
**Class**  
`BeforeRenderLayoutEvent`, getters `getLayoutName()`, `getData()`, `getComponent()`, modify the data with `updateData(array $data)`.

**Description**  
Before a layout is rendered in the DPAttachments context.

**Arguments**
1. name  
The name of the layout file.
2. data  
The data for the layout file. Modify the data as it is used for rendering.
3. component  
The component which executes the action, so no booting is needed.

#### onDPAttachmentsAfterRenderLayout
**Class**  
`AfterRenderLayoutEvent`, getters `getLayoutName()`, `getData()`, `getContent()`, `getComponent()`, modify the content with `updateContent(string $content)`.

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
**Class**  
`CheckPermissionEvent`, getters `getAction()`, `getContext()`, `getItemId()`, grant the permission with `allow()`.

**Description**  
When a permission is checked. If a listener calls `allow()` (or sets the argument `allowed` to `true`), then no further permission checks are done for the item.

**Arguments**
1. action  
The name of the action.
2. context  
The context of the check.
3. item_id  
The item to check the permissions for.

### Content events for attachments
When a list of attachments is rendered, the following core Joomla content events are dispatched for every attachment, between `onDPAttachmentsBeforeProcessList` and `onDPAttachmentsAfterProcessList`. The context is always `com_dpattachments.attachment`, the arguments are `context`, `subject` (the attachment), `params` (the options as `Joomla\Registry\Registry`) and `page` (always 0).

| Event | Class | Output |
|---|---|---|
| onContentPrepare | `Joomla\CMS\Event\Content\ContentPrepareEvent` | Modify the attachment directly. |
| onContentAfterTitle | `Joomla\CMS\Event\Content\AfterTitleEvent` | Rendered after the title of the attachment. |
| onContentBeforeDisplay | `Joomla\CMS\Event\Content\BeforeDisplayEvent` | Rendered before the attachment. |
| onContentAfterDisplay | `Joomla\CMS\Event\Content\AfterDisplayEvent` | Rendered after the attachment. |

For the display events, add your HTML string to the `result` argument with `$event->addResult($html)`. All results are joined and printed in the attachment layout.
