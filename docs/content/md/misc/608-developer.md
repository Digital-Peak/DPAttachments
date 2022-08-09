### Introduction
DPAttachments can be integrated easily into your existing Joomla extension, doesn't matter if it is a component, module or plugin. Just write the following line in a view file and DPAttachments does the rest. The `$app` is sometimes not available, then you can use `Joomla\CMS\Factory::getApplication`.

`echo $app->bootComponent('dpattachments')->render('com_demo.item', $object->id);`

### Write your own plugin
If you are not the developer of a Joomla extension but you want to integrate DPAttachments into it then you can write your own plugin. The only requirement is that the extensions fires an event where the plugin can be an observer of. Have a look at the [content plugin of DPAttachments](https://github.com/Digital-Peak/DPAttachments/tree/main/plg_content_dpattachments) as example, it integrates into the common content events. Basically you need the following code when there is an component com_awesomeimages with an entity image:

```
class AwesomeImages extends CMSPlugin
{
	/** @var CMSApplication $app */
	protected $app;

	public function onImageAfterDisplay($image)
	{
		return $this->app->bootComponent('dpattachments')->render('com_awesomeimages.image', $image->id);
	}
}
```
