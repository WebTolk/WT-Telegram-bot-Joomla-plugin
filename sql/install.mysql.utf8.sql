CREATE TABLE `#__plg_system_wttelegrambot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) UNSIGNED NOT NULL COMMENT 'Telegram message id',
  `chat_id` bigint(20) NOT NULL COMMENT 'Telegram chat id',
  `context` varchar(150) DEFAULT NULL COMMENT 'Joomla context like com_content.article',
  `item_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Joomla item id',
  `date` int(10) UNSIGNED NOT NULL COMMENT 'Date in UNIX format',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;