<?php

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Request;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

use function Yansongda\Pay\get_epay_config;

class RadarSignPlugin implements PluginInterface
{
    /**
     * @param Rocket $rocket
     * @param Closure $next
     * @return Rocket
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[epay][RadarSignPlugin] 插件开始装载', ['rocket' => $rocket]);

        $this->sign($rocket);

        $this->reRadar($rocket);

        Logger::info('[epay][RadarSignPlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @param Rocket $rocket
     * @return void
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function reRadar(Rocket $rocket): void
    {
        $params = $rocket->getParams();

        $rocket->setRadar(new Request(
            $this->getMethod($params),
            $this->getUrl($params),
            $this->getHeaders(),
            $this->getBody($rocket->getPayload()),
        ));
    }

    /**
     * @param Rocket $rocket
     * @return void
     */
    protected function sign(Rocket $rocket): void
    {
        $this->formatPayload($rocket);

        $sign = $this->getSign($rocket);

        $rocket->mergePayload(['sign' => $sign]);
    }

    protected function formatPayload(Rocket $rocket): void
    {
        $payload = $rocket->getPayload()->filter(fn ($v, $k) => '' !== $v && !is_null($v) && 'sign' != $k);

        $contents = array_filter($payload->get('biz_content', []), fn ($v, $k) => !Str::startsWith(strval($k), '_'), ARRAY_FILTER_USE_BOTH);

        $rocket->setPayload(
            $payload->merge(['biz_content' => json_encode($contents)])
        );
    }

    /**
     * @param Rocket $rocket
     * @return string
     */
    protected function getSign(Rocket $rocket): string
    {
        return "sign";
    }

    /**
     * @param array $params
     * @return string
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function getUrl(array $params): string
    {
        $config = get_epay_config($params);

        return $config['pay_url'];
    }

    /**
     * @return string
     */
    protected function getMethod(): string
    {
        return strtoupper($params['_method'] ?? 'POST');
    }

    /**
     * @param Collection $payload
     * @return string
     */
    protected function getBody(Collection $payload): string
    {
        return $payload->query();
    }
}