<?php
/**
 * @package    System - WT Telegram bot
 * @version    1.1.1
 * @Author     Sergey Tolkachyov, https://web-tolk.ru
 * @copyright  (c) 2024 - September 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @since      1.0.0
 */

namespace Joomla\Plugin\System\Wttelegrambot\Fields;

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;

\defined('_JEXEC') or die;

class ChatidinfoField extends TextField
{

    protected $type = 'Chatidinfo';

    public function getInput()
    {
        $html = parent::getInput();

        $data               = $this->form->getData();
        $params             = new Registry($data->get('params'));
        $telegram_api_token = $params->get('telegram_api_token', '');
        $telegram_chat_id = $params->get('telegram_chat_id', '');

        if (!empty($telegram_api_token) && !empty($telegram_chat_id))
        {
            $http = (new HttpFactory())->getHttp();
            $tg_query = [
                'chat_id'    => $telegram_chat_id,
            ];
            $url = new Uri('https://api.telegram.org/bot' . $params->get('telegram_api_token') . '/getChat');
            $url->setQuery($tg_query);
            $result = $http->get($url);

            if ($result === null || $result->getStatusCode() !== 200) {
                return $html;
            }
            $chat_info = json_decode((string)$result->getBody());

            if ($chat_info->ok && property_exists($chat_info, 'result'))
            {
                $html .= '
				<div class="valid-feedback d-block">
				<span class="badge bg-primary">Title</span><span class="badge bg-info">'.$chat_info->result->title.'</span> <span class="badge bg-primary">is_forum</span><span class="badge bg-info">'.( ($chat_info->result->is_forum === true) ? Text::_('JYES') : Text::_('JNO')).'
				</div>
				';
            }
        }
        return $html;
    }
}