# WT Telegram bot Joomla plugin
A plugin for sending messages from the Joomla site to the Telegram bot. The plugin provides a simple way to send information from Joomla to chats and channels using messages to the bot. Developers can use it for their extensions.

# How to use?
First you need to:
- create a bot in Telegram,
- add it to your chat or channel
- appoint him as an administrator with the right to post messages

There are plenty of instructions on this on the Internet. To configure the plugin, you need to take the Telegram API token from BotFather, and also find out the id of the chat or Telegram channel, where Joomla will send messages using the bot. These can be both private messages and channels.

Provider plugins for sending messages from Joomla to Telegram
At the moment, the following plugins have been created:

- **WT Telegram bot - Content** - sends an image and the introductory text (or part of the full text) of Joomla articles to Telegram.
- **WT Telegram bot - SW JProjects** - sends information about projects and their versions, documentation from the SW JProjects component to the Telegram chat or channel.
- **WT Telegram bot - JoomShopping** - sends information about new orders in the JoomShopping online store to the Telegram chat or channel.

Links to them are below the page.
> This plugin is useless by itself if you are not a Joomla developer. Developers can create plug-in providers that will send messages with the necessary data.

# Code example
Use this code in your extensions.
```php
<?php
$event = \Joomla\CMS\Event\AbstractEvent::create('onWttelegrambotSendMessage',

			[
				'subject' => $this,
				'message' => $message,
				'images'  => $images,
				'link'    => $link,
				'params'  => $message_params
			]
		);
		$this->getApplication()->getDispatcher()->dispatch($event->getName(), $event);

		return $event->getArgument('result', []);
```

# Code description
- `$message` - the text of the message, cleared of HTML tags. Only simple text formatting tags are allowed, according to the documentation Telegram API: Formatting options
- `$images` - array of paths to images like /images/image.jpg. The path must be relative and start with a slash sign /.
- `$link` - The HTML code of the link, if necessary. <a href="https://my-site.com/my-link>My link title</a>
- `$message_params` - an array with parameters for a specific message.
- `$message_params['context']` - the context of sending a message in the Joomla format like com_content.article.
- `$message_params['item_id']` - Joomla item id if exists. Article or product id for example.
- `$message_params['chat_id']` - chat or channel id, different from the one specified by default in the plugin settings. Different areas of the site can send messages to different chats or channels.

If the message is successfully sent to Telegram, the information about the message is stored in the database in the table `#__plg_system_wttelegrambot`. The message ID in Telegram, the chat or channel id, and the date of sending in UNIX format are saved. Also, if passed, the context and the entity id are saved. In this way, you can further track the history of transmitted messages in the database by context and id.

# System requirements
PHP 8.0+. Joomla 4.3+
