<?php
/**
 * @package    System - WT Telegram bot
 * @version    1.1.1
 * @Author     Sergey Tolkachyov, https://web-tolk.ru
 * @copyright  (c) 2024 - September 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @since      1.0.0
 */

namespace Joomla\Plugin\System\Wttelegrambot\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Http\HttpFactory;
use Joomla\CMS\Uri\Uri;

// No direct access
\defined('_JEXEC') or die;

final class Wttelegrambot extends CMSPlugin implements SubscriberInterface
{
	use DatabaseAwareTrait;

	protected $autoloadLanguage = true;

	/**
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 4.1.0
	 *
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onWttelegrambotSendMessage' => 'onWttelegrambotSendMessage'
		];
	}

	public function onWttelegrambotSendMessage(Event $event): void
	{
		/** @var array $message_params Additional message params for current message */
		$message_params = $event->getArgument('params');

		if (!$this->params->get('telegram_api_token') || !$this->params->get('telegram_chat_id'))
		{
			return;
		}

		$message = $event->getArgument('message');
		if(!empty($message))
		{
			$message = $this->prepareMessageForTelegram($message);
		}

		$link = $event->getArgument('link');
		if(!empty($link))
		{
			$link = $this->prepareMessageForTelegram($link);
		}

		$images = $event->getArgument('images');

		$telegram_method = 'sendMessage';
		$http            = (new HttpFactory())->getHttp([]);

        $chat_id = $this->params->get('telegram_chat_id');
        $message_thread_id = $this->params->get('message_thread_id', null);
        // Отдельные сообщения могут быть отправлены не в основной канал
        if(array_key_exists('chat_id', $message_params) && !empty($message_params['chat_id']))
        {
            $chat_id = $message_params['chat_id'];

            if(array_key_exists('message_thread_id',$message_params) && !empty($message_params['message_thread_id']))
            {
                $message_thread_id = $message_params['message_thread_id'];
            }
        }

        $tg_query = [
            'chat_id'    => $chat_id,
            'parse_mode' => 'html',
        ];

        if($message_thread_id) {
            $tg_query['message_thread_id'] = $message_thread_id;
            $tg_query['chat_id'] = $tg_query['chat_id'] .'_'.$message_thread_id;
        }

		//	    Отправка вложений
		if (!empty($images))
		{

			if (count($images) == 1)
			{
				// Одно фото

				$image = $images[0];

				$imageUrl = new Uri(Uri::root());

				// Путь к картинке должен начинаться со слеша '/'
				$firstSymbol = substr($image, 0);
				if ($firstSymbol != '/')
				{
					$image = '/' . $image;
				}

				$imageUrl->setPath($image);

				$tg_query['photo'] = $imageUrl->toString();

				// Подпись к медиа-файлам - 1024 символа
				// Запас для текста ссылки
				if (strlen($message) > 1000)
				{
					$message = mb_substr($message, 0, 1000, 'utf-8');
					$message = $message . '...';

				}

				if (!empty($link))
				{
					$message = $message . PHP_EOL . $link;
				}

				$tg_query['caption'] = $message;

				$telegram_method = 'sendPhoto';

			}
			else
			{

//				// Несколько фото
//				$media = [];
//				foreach ($images as $key => $image)
//				{
//					if ($key > 9)
//					{
//						break;
//					}
//
//					$image    = str_replace('//', '/', $image); // Удаляем двойные слеши в пути, если есть
//					$imageUrl = new Uri(Uri::root());
//					$imageUrl->setPath($image);
//
//					$media[$key]['type']  = 'photo';
//					if($key == 0)
//					{
//						$media[$key]['caption']  = $message;
//					}
//					$media[$key]['media'] = $imageUrl->toString();
//				}
//
//				$tg_query['media'] = json_encode($media);
//				$telegram_method   = 'sendMediaGroup';
			}

		}
		else
		{
			// Просто текст, если нет картинок

			if (!empty($link))
			{
				$message = $message . PHP_EOL . $link;
			}
			$tg_query['text'] = urlencode($message);
		}


		$url = new Uri('https://api.telegram.org/bot' . $this->params->get('telegram_api_token') . '/' . $telegram_method);

		try
		{
			if (!empty($images))
			{
				// Отправка картинок
				$result = $http->post($url, $tg_query);
			}
			else
			{
				// Отправка текста
				$url->setQuery($tg_query);
				$result = $http->get($url);
			}

			if ($result && $result->code == 200)
			{
				$result_body = json_decode($result->body);
				$this->saveTelegramMessageId($result_body, $message_params);
			}

			$event->setArgument('result', json_decode($result->body));
		}
		catch (\Exception $e)
		{

		}
	}

	/**
	 * HTML to Text converter/formatter for Telegram Bot API
	 * 
	 * @param   string   $message
	 *
	 *
	 * @since 1.0.0
	 * @link       https://web-tolk.ru
	 */
	public function prepareMessageForTelegram(string $message):string
	{
		\JLoader::registerNamespace('WPSocio\TelegramFormatText', JPATH_SITE.'/plugins/system/wttelegrambot/src/Lib/telegramformattext/src');

		$options = [
			'format_to' => 'HTML', // HTML, Markdown, Text
		];
		$converter = new \WPSocio\TelegramFormatText\HtmlConverter($options);

		// The text is now safe to be sent to Telegram
		$text = $converter->convert($message);
		return $text;
	}


	/**
	 * @param          $telegram_response
	 * @param   array  $message_params  Additional message params for current message
	 *
	 *
	 * @since 1.0.0
	 * @link       https://web-tolk.ru
	 */
	private function saveTelegramMessageId($telegram_response, array $message_params = []): void
	{
		
		if (!$telegram_response->ok)
		{
			return;
		}

		$message_id = (int) $telegram_response->result->message_id;
		$chat_id    = (int) $telegram_response->result->chat->id;
		$context    = (array_key_exists('context', $message_params) && !empty($message_params['context'])) ? (string) $message_params['context'] : null;
		$item_id    = (array_key_exists('item_id', $message_params) && !empty($message_params['item_id'])) ? (int) $message_params['item_id'] : null;
		$date       = (int) $telegram_response->result->date;

		$db = $this->getDatabase();

		$query = $db->getQuery(true);
		$query->insert($db->quoteName('#__plg_system_wttelegrambot'));

		// Insert columns.
		$columns = [
			'message_id',
			'chat_id',
			'context',
			'item_id',
			'date'
		];

		// Prepare the insert query.
		$query->columns($db->quoteName($columns))
			->values(':message_id, :chat_id, :context, :item_id, :date');


		// Bind values
		$query->bind(':message_id', $message_id, ParameterType::INTEGER)
			->bind(':chat_id', $chat_id, ParameterType::INTEGER)
			->bind(':context', $context, $context ? ParameterType::STRING : ParameterType::NULL)
			->bind(':item_id', $item_id, $item_id ? ParameterType::INTEGER : ParameterType::NULL)
			->bind(':date', $date, ParameterType::INTEGER);


		// Set the query using our newly populated query object and execute it.
		$db->setQuery($query);
		
		$db->execute();

	}

}