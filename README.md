DPAttachments
=============
This is the official DPAttachments Github repository. DPAttachments is a slick Joomla attachment
extension which provides drag and drop or copy paste file uploads for articles, DPCalendar events
or DPCases cases, basically every component which triggers an onContentAfterDisplay event.

PREREQUISITS
------------
- Joomla 3.1
- mysql >= 5.0.0
- PHP >= 5.3.0

INSTALLATION
------------
Just install the downloaded zip file trough the Joomla extension manager and make sure the plugins are
enabled.

INTEGRATION
------------
If you are an extension developer you just need the following code snippet to integrate DPAttachments
into your extension:

```php
if (JLoader::import('components.com_dpattachments.libraries.vendor.autoload', JPATH_ADMINISTRATOR)) {
    echo \DPAttachments\Helper\Core::render('com_demo.item', $object->id);
}
```

UPGRADE
-------
To upgrade DPAttachments from an older version just install the downloaded zip file trough the Joomla
extension manager.

DOCUMENTATION
-------------
Check http://joomla.digital-peak.com for more documentation.


Have fun
The Digital Peak team
