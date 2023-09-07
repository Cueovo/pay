<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay\Trade;

use Symfony\Component\Mime\Email;
use Yansongda\Pay\Direction\ResponseDirection;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Plugin\Epay\GeneralPlugin;
use Yansongda\Pay\Rocket;

class PagePayPlugin extends GeneralPlugin
{
    protected function getUri(Rocket $rocket): string
    {
        $params = $rocket->getParams();

        if (empty($params['out_trade_no'])
            || empty($params['name'])
            || empty($params['money'])
            || empty($params['type'])) {
            throw new InvalidParamsException(Exception::MISSING_NECESSARY_PARAMS);
        }

        return 'submit.php';
    }

    protected function doSomething(Rocket $rocket): void
    {
        $rocket->setDirection(ResponseDirection::class);
    }
}