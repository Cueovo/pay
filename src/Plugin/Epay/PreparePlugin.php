<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use function Yansongda\Pay\get_epay_config;
use function Yansongda\Pay\get_tenant;

class PreparePlugin implements PluginInterface
{

    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[epay][PreparePlugin] 插件开始装载',  ['rocket' => $rocket]);

        $rocket->mergePayload($this->getPayload($rocket->getParams()));

        Logger::info('[epay][PreparePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    protected function getPayload(array $params): array
    {
        $config = get_epay_config($params);

        return [
            'pid' => $config['pay_id'],
            'partner' => $config['pay_id'],
            'key' => $config['pay_key'],
            'sign_type' => strtoupper('MD5'),
            'input_charset' => strtolower('utf-8'),
            'transport' => 'http',
            'apiurl' => $config('pay_url'),
            'notify_url' => $this->getNotifyUrl($params, $config),
            'return_url' => $this->getReturnUrl($params, $config),
        ];
    }

    protected function getReturnUrl(array $params, array $config): string
    {
        if (!empty($params['_return_url'])) {
            return $params['_return_url'];
        }

        return $config['return_url'] ?? '';
    }

    protected function getNotifyUrl(array $params, array $config): string
    {
        if (!empty($params['_notify_url'])) {
            return $params['_notify_url'];
        }

        return $config['notify_url'] ?? '';
    }
}