<?php

namespace CDEK\Actions\Admin\Settings;

use CDEK\CdekApi;
use CDEK\Helpers\LogHelper;
use CDEK\RegistrySingleton;
use CDEK\Settings;
use Exception;

class SaveSettingsAction
{
    public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();

        $redirectUrl = $registry->get('url')
                                ->link(
                                    'extension/shipping/cdek_official',
                                    'user_token=' . $registry->get('session')->data['user_token'],
                                    true,
                                );

        /** @var \Response $response */
        $response = $registry->get('response');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $response->redirect($redirectUrl);
            return;
        }

        $modelSetting = $registry->get('model_setting_setting');
        $settings     = new Settings;
        $settings->init($_POST);
        $cdekApi      = new CdekApi($settings);

        $modelSetting->editSetting('cdek_official', $_POST);
        if (!$cdekApi->checkAuth()){
            $response->redirect($redirectUrl);
            return;
        }

        /** @var \Session $session */
        $session = $registry->get('session');
        /** @var \Language $language */
        $language = $registry->get('language');

        try {
            $settings->validate();
            $session->data['success'] = $language->get('text_success');
        } catch (Exception $exception) {
            LogHelper::write('Validation failed: ' .
                             $language->get($exception->getMessage()));
            $session->data['error_warning'] = $language->get('error_permission') .
                                              $language->get($exception->getMessage());
        }

        $response->redirect($redirectUrl);
    }
}
