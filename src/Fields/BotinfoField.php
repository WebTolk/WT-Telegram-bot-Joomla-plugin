<?php
/**
 * @package    System - WT Telegram bot
 * @version    1.1.0
 * @Author     Sergey Tolkachyov, https://web-tolk.ru
 * @copyright  (c) 2024 - September 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @since      1.0.0
 */

namespace Joomla\Plugin\System\Wttelegrambot\Fields;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\NoteField;
use Joomla\CMS\Language\Text;
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;


class BotinfoField extends NoteField
{

	protected $type = 'Botinfo';

	public function getLabel()
	{
		$data               = $this->form->getData();
		$params             = new Registry($data->get('params'));
		$telegram_api_token = $params->get('telegram_api_token', '');

		$html = '';
		if ($telegram_api_token)
		{
			$http = (new HttpFactory())->getHttp();

			$result   = $http->get('https://api.telegram.org/bot' . $telegram_api_token . '/getMe')->body;
			$bot_info = json_decode($result);

			if ($bot_info->ok && property_exists($bot_info, 'result'))
			{
				$html = "</div>
				<div class='card shadow-sm'>
				<div class='card-body'>
				<h5 class='h5'><span class='badge bg-success'><span class='icon icon-ok m-0'></span></span> " . $bot_info->result->first_name . "</h5>
				<p>			
					<span class='me-2'><span class='badge bg-info'>ID:</span><span class='badge bg-success'>" . $bot_info->result->id . "</span></span>	
					<span class='me-2'><span class='badge bg-info'>Username</span><span class='badge bg-success'>" . $bot_info->result->username . "</span></span>
					<a href='https://t.me/" . $bot_info->result->username . "' target='_blank'>https://t.me/" . $bot_info->result->username . "</a>	
				</p>
				</div>
				</div>
				<div>
				";
			}
		}

		return $html;
	}

	/**
	 * Method to get the field input markup for a spacer.
	 * The spacer does not have accept input.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		return ' ';
	}

}