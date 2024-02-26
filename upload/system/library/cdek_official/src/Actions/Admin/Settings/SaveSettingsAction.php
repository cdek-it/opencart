<?php

namespace CDEK\Actions\Admin\Settings;

use CDEK\Config;
use CDEK\Helpers\LogHelper;
use CDEK\RegistrySingleton;
use CDEK\SettingsSingleton;
use CDEK\Transport\CdekApi;
use Exception;

class SaveSettingsAction
{
    public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();

        $redirectUrl = $registry->get('url')
                                ->link(
                                    'extension/shipping/' . Config::DELIVERY_NAME,
                                    'user_token=' . $registry->get('session')->data['user_token'],
                                    true,
                                );

        /** @var \Response $response */
        $response = $registry->get('response');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $response->redirect($redirectUrl);
            return;
        }

        $registry->get('load')->model('setting/setting');

        $settings     = SettingsSingleton::getInstance($_POST);

        if (!CdekApi::checkAuth()){
            $response->redirect($redirectUrl);
            return;
        }

        /** @var \Session $session */
        $session = $registry->get('session');
        /** @var \Language $language */
        $language = $registry->get('language');

        try {
            $settings->save();
            $settings->validate();
            $session->data['success'] = $language->get('text_success');
        } catch (Exception $exception) {
            LogHelper::write('Validation failed: ' .
                             $language->get($exception->getMessage()));
            $session->data['error_warning'] = $language->get($exception->getMessage());
        }

        $response->redirect($redirectUrl);
    }
}
