<?php

namespace CDEK\Actions\Admin\Settings;

use CDEK\App;
use CDEK\CdekConfig;
use CDEK\CdekHelper;
use CDEK\RegistrySingleton;
use Document;
use Exception;
use Language;
use Loader;
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
        $document->addScript('//cdn.jsdelivr.net/npm/@cdek-it/widget@' . CdekConfig::MAP_VERSION);

        $loader->model('setting/setting');

        $app = new App([], DIR_APPLICATION);
        $app->handleAjaxRequest();
        $app->run();

        $app->checkState($app->data);
        $userToken = $registry->get('session')->data['user_token'];

        $app->data['action']      = $url->link('extension/shipping/cdek_official/store', "user_token=$userToken", true);
        $app->data['map_service'] = $url->link("extension/shipping/cdek_official/map&user_token=$userToken", '', true);
        $app->data['cancel']      = $url->link('extension/shipping', "user_token=$userToken", true);

        $app->data['header']      = $loader->controller('common/header');
        $app->data['column_left'] = $loader->controller('common/column_left');
        $app->data['footer']      = $loader->controller('common/footer');

        $app->data['breadcrumbs'] = [
            [
                'text' => $language->get('text_home'),
                'href' => $url->link('common/dashboard', "user_token=$userToken", true),
            ],
            [
                'text' => $language->get('text_extension'),
                'href' => $url->link('marketplace/extension', "user_token=$userToken&type=shipping", true),
            ],
            [
                'text' => $language->get('heading_title'),
                'href' => $url->link('extension/shipping/cdek_official', "user_token=$userToken", true),
            ],
        ];

        $app->data['city'] = $app->data['map_city'] ?? 'Москва';

        $officeLocality                     = CdekHelper::getLocality($app->settings->shippingSettings->shippingPvz);
        $app->data['office_code_selected']  = is_object($officeLocality) && property_exists($officeLocality, 'code') ?
            $officeLocality->code : null;
        $addressLocality
                                            = CdekHelper::getLocality($app->settings->shippingSettings->shippingCityAddress);
        $app->data['address_code_selected'] = is_object($addressLocality) &&
                                              property_exists($addressLocality, 'formatted') ?
            $addressLocality->formatted : null;
        $app->data['apikey']                = $app->settings->authSettings->apiKey;
        $app->data['map_lang']              = $app->settings->authSettings->mapLangCode ?? 'rus';

        $registry->get('response')->setOutput($loader->view('extension/shipping/cdek_official', $app->data));
    }
}
