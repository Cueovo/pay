<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay\Trade;

use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Plugin\Epay\GeneralPlugin;
use Yansongda\Pay\Rocket;

class PagePayPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        return 'submit.php';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setDirection(ResponseDirection::class);
    }
}