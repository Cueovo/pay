<?php

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Direction\NoHttpRequestDirection;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;

use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;
use function Yansongda\Pay\verify_epay_sign;

class CallbackPlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[epay][CallbackPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->formatPayload($rocket);

        $params = $rocket->getParams();
        $sign = $params['sign'] ?? false;

        if (!$sign) {
            throw new InvalidResponseException(Exception::INVALID_RESPONSE_SIGN, 'INVALID_RESPONSE_SIGN', $params);
        }

        verify_epay_sign($params, $rocket->getPayload()->sortKeys()->toString());

        $rocket->setDirection(NoHttpRequestDirection::class)
            ->setDestination($rocket->getPayload());

        Logger::info('[epay][CallbackPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function formatPayload(Rocket $rocket): void
    {
        $payload = (new Collection($rocket->getParams()))
            ->filter(fn ($v, $k) => 'sign' != $k && 'sign_type' != $k && !Str::startsWith($k, '_'));
        $rocket->setPayload($payload);
    }
}