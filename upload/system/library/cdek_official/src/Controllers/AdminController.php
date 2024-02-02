<?php

namespace CDEK\Controllers;

use CDEK\Actions\Admin\Installer\InstallExtensionAction;
use CDEK\Actions\Admin\Installer\RemoveExtensionAction;
use CDEK\Actions\Admin\Order\CreateOrderAction;
use CDEK\Actions\Admin\Order\DeleteOrderAction;
use CDEK\Actions\Admin\Order\GetOrderInfoScriptsAction;
use CDEK\Actions\Admin\Order\GetOrderInfoTabAction;
use CDEK\Actions\Admin\Order\GetWaybillAction;
use CDEK\Actions\Admin\Settings\GetOfficesAction;
use CDEK\Actions\Admin\Settings\RenderSettingsPageAction;
use CDEK\Actions\Admin\Settings\SaveSettingsAction;
use CDEK\Contracts\ControllerContract;
use Exception;
use JsonException;
use Request;

class AdminController extends ControllerContract
{

    final public function uninstall(): void
    {
        (new RemoveExtensionAction)();
    }

    /**
     * @throws Exception
     */
    final public function index(): void
    {
        (new RenderSettingsPageAction)();
    }

    final public function store(): void
    {
        (new SaveSettingsAction)();
    }

    /**
     * @throws JsonException
     */
    final public function map(): void
    {
        (new GetOfficesAction)();
    }

    /**
     * @throws Exception
     */
    final public function create(): void
    {
        /** @var Request $request */
        $request = $this->registry->get('request');
        (new CreateOrderAction)($request->get['order_id'],
                                $request->post['width'],
                                $request->post['height'],
                                $request->post['length']);
    }

    final public function delete(): void
    {
        (new DeleteOrderAction)($this->registry->get('request')->get['order_id']);
    }

    /**
     * @noinspection PhpUnused
     * @throws JsonException
     */
    final public function waybill(): void
    {
        (new GetWaybillAction)($this->registry->get('request')->get['order_id']);
    }

    /**
     * @noinspection PhpUnused
     */
    final public function orderInfoScripts(): void
    {
        (new GetOrderInfoScriptsAction)();
    }

    /**
     * @throws Exception
     * @noinspection PhpUnused
     */
    final public function orderInfo(string &$route, array &$data): void
    {
        $data['tabs'][] = (new GetOrderInfoTabAction)((int)$this->registry->get('request')->get['order_id']);
    }

    final public function install(): void
    {
        (new InstallExtensionAction)();
    }
}
