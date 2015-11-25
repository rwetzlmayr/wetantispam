INSERT IGNORE INTO `engine4_core_modules` (`name`, `title`, `description`, `version`, `enabled`, `type`) VALUES  ('wetantispam', 'Wet AntiSpam', 'Spam filter', '1.0.0', 1, 'extra') ;

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
  ('core_admin_main_plugins_wetantispam', 'wetantispam', 'Wet Antispam', '', '{"route":"admin_default","module":"wetantispam","controller":"index"}', 'core_admin_main_plugins', '', 999);
