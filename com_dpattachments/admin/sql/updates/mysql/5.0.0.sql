ALTER TABLE `#__dpattachments` ADD `params` TEXT NULL;

ALTER TABLE `#__dpattachments` CHANGE `created` `created` DATETIME NULL DEFAULT NULL;
ALTER TABLE `#__dpattachments` CHANGE `modified` `modified` DATETIME NULL DEFAULT NULL;
ALTER TABLE `#__dpattachments` CHANGE `checked_out_time` `checked_out_time` DATETIME NULL DEFAULT NULL;
ALTER TABLE `#__dpattachments` CHANGE `publish_up` `publish_up` DATETIME NULL DEFAULT NULL;
ALTER TABLE `#__dpattachments` CHANGE `publish_down` `publish_down` DATETIME NULL DEFAULT NULL;
