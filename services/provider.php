<?php
/**
 * @package    System - WT Telegram bot
 * @version    1.1.1
 * @Author     Sergey Tolkachyov, https://web-tolk.ru
 * @copyright  (c) 2024 - September 2025 Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @since      1.0.0
 */

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Wttelegrambot\Extension\Wttelegrambot;

\defined('_JEXEC') or die;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param Container $container The DI container.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $subject = $container->get(DispatcherInterface::class);
                $config = (array)PluginHelper::getPlugin('system', 'wttelegrambot');
                $plugin = new Wttelegrambot($subject, $config);
                $plugin->setApplication(\Joomla\CMS\Factory::getApplication());
	            $plugin->setDatabase(\Joomla\CMS\Factory::getContainer()->get('DatabaseDriver'));
                return $plugin;
            }
        );
    }
};