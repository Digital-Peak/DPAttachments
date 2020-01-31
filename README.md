# DPAttachments
This is the official DPAttachments Github repository. DPAttachments is a slick Joomla attachment extension which provides drag and drop or copy paste file uploads for articles, DPCalendar events or DPCases cases, basically every component which triggers an onContentAfterDisplay event.

## PREREQUISITS
- Joomla 3.8
- mysql >= 5.0.0
- PHP >= 5.5.0

## INSTALLATION
Download the file from [here](https://joomla.digital-peak.com/download/dpattachments) and install the zip file through the Joomla extension manager. After the installation make sure the plugins are enabled.

## INTEGRATION
If you are an extension developer you just need the following code snippet to integrate DPAttachments into your extension:

```php
if (JLoader::import('components.com_dpattachments.vendor.autoload', JPATH_ADMINISTRATOR)) {
    echo \DPAttachments\Helper\Core::render('com_demo.item', $object->id);
}
```

## UPGRADE
To upgrade DPAttachments from an older version just install the downloaded zip file through the Joomla extension manager or by web update.

## DOCUMENTATION
Check [joomla.digital-peak.com](https://joomla.digital-peak.com/documentation/dpattachments) for more documentation.

## Development corner
If you are cloning this repository, then clone also the [DPDocker project](https://github.com/Digital-Peak/DPDocker) into the same folder as DPAttachments and build the images. There are several tasks you can run then:

### Build packages
Creates the installable packages.

`DPDocker/build/run.sh DPAttachments`

### Install JS dependencies
Installs the Javascript dependencies.

`DPDocker/npm/run-install.sh DPAttachments`

### Build assets
Builds the assets.

`DPDocker/npm/run-build.sh DPAttachments all`

### Install PHP dependencies
Installs the PHP dependencies.

`DPDocker/composer/run-install.sh DPAttachments`

### Run system tests
Runs the system tests.

`DPDocker/tests/run-system-tests.sh DPAttachments`

### Start a web server
Start a web server with DPAttachments installed.

`DPDocker/webserver/run.sh`

## Notes
License GPL v3

Have fun
The Digital Peak team
