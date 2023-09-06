<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Exception\InvalidResponseException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;

use function Yansongda\Pay\should_do_http_request;
use function Yansongda\Pay\verify_epay_sign;

class LaunchPlugin implements PluginInterface
{
    /**
     * @param Rocket $rocket
     * @param Closure $next
     * @return Rocket
     * @throws InvalidResponseException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);
        Logger::debug('[epay][LaunchPlugin] 插件开始装载', ['rocket' => $rocket]);

        if (should_do_http_request($rocket->getDirection())) {
            $response = Collection::wrap($rocket->getDestination());
            $result = $response->get($rocket->getPayload());

            $this->verifySign($rocket->getParams(), $response, $result);

            $rocket->setDestination(Collection::wrap($result));
        }

        Logger::info('[alipay][LaunchPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    /**
     * @param array $params
     * @param Collection $response
     * @param array|null $result
     * @return void
     * @throws InvalidResponseException
     * @throws \Yansongda\Pay\Exception\InvalidConfigException
     */
    protected function verifySign(array $params, Collection $response, ?array $result): void
    {
        $sign = $response->get('sign', '');

        if ('' === $sign || is_null($result)) {
            throw new InvalidResponseException(Exception::INVALID_RESPONSE_SIGN, 'Verify Epay Response Sign Failed: sign is empty', $response);
        }

        verify_epay_sign($params, json_encode($result, JSON_UNESCAPED_UNICODE), $sign);
    }

}