<?php

namespace CDEK\Actions\Admin\Settings;

use CDEK\Config;
use CDEK\Models\Currency;
use CDEK\Models\Tariffs;
use CDEK\RegistrySingleton;
use CDEK\SettingsSingleton;
use CDEK\Transport\CdekApi;
use Document;
use Exception;
use Language;
use Loader;
use Session;
use Url;

class RenderSettingsPageAction
{
    /**
     * @throws Exception
     */
    public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();
        /** @var Document $document */
        $document = $registry->get('document');
        /** @var Loader $loader */
        $loader = $registry->get('load');
        /** @var Url $url */
        $url = $registry->get('url');

        $loader->language('extension/shipping/cdek_official');

        /** @var Language $language */
        $language = $registry->get('language');

        $document->setTitle($language->get('heading_title'));
        $document->addStyle('view/stylesheet/cdek_official/settings_page.css');
        $document->addScript('//cdn.jsdelivr.net/npm/@cdek-it/widget@' . Config::MAP_VERSION);

        $loader->model('setting/setting');
        $loader->model('localisation/length_class');
        $loader->model('localisation/weight_class');

        /** @var Session $session */
        $session = $registry->get('session');

        $settings = SettingsSingleton::getInstance()->__serialize();

        $data = [
            'success'       => $session->data['success'] ?? '',
            'error_warning' => $session->data['error_warning'] ?? '',

            'action'      => $url->link('extension/shipping/cdek_official/store', "user_token={$session->data['user_token']}", true),
            'map_service' => $url->link("extension/shipping/cdek_official/map&user_token={$session->data['user_token']}", '', true),
            'cancel'      => $url->link('extension/shipping', "user_token={$session->data['user_token']}", true),

            'header'      => $loader->controller('common/header'),
            'column_left' => $loader->controller('common/column_left'),
            'footer'      => $loader->controller('common/footer'),

            'breadcrumbs' => [
                [
                    'text' => $language->get('text_home'),
                    'href' => $url->link('common/dashboard', "user_token={$session->data['user_token']}", true),
                ],
                [
                    'text' => $language->get('text_extension'),
                    'href' => $url->link('marketplace/extension', "user_token={$session->data['user_token']}&type=shipping", true),
                ],
                [
                    'text' => $language->get('heading_title'),
                    'href' => $url->link('extension/shipping/cdek_official', "user_token={$session->data['user_token']}", true),
                ],
            ],

            'tariffs' => Tariffs::getTariffList(),
            'currencies' => Currency::listCurrencies(),
            'auth_status' => CdekApi::checkAuth(),
            'weight_classes' => $registry->get('model_localisation_weight_class')->getWeightClasses(),
            'length_classes' => $registry->get('model_localisation_length_class')->getLengthClasses(),
        ];
        unset($session->data['success'], $session->data['error_warning']);

        $registry->get('response')->setOutput($loader->view('extension/shipping/cdek_official/settings',
                                                            array_merge($data, $settings)));
    }
}
