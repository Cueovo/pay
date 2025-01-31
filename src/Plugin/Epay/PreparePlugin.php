<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Str;
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
        $tenant = get_tenant($params);
        $config = get_epay_config($params);

        $init = [
            'pid' => $config['pay_id'],
            'sign_type' => strtoupper('MD5'),
            'notify_url' => $this->getNotifyUrl($params, $config),
            'return_url' => $this->getReturnUrl($params, $config),
        ];

        return array_merge(
            $init,
            array_filter($params, fn ($v, $k) => !Str::startsWith(strval($k), '_'), ARRAY_FILTER_USE_BOTH),
        );
    }

    protected function getMethod(array $params, array $config): string
    {
        if (!empty($params['_method'])) {
            return $params['_method'];
        }

        return $config['method'] ?? 'POST';
    }

    protected function getMethod(array $params, array $config): string
    {
        if (!empty($params['_method'])) {
            return $params['_method'];
        }

        return $config['method'] ?? 'POST';
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