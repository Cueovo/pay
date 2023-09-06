<?php

declare(strict_types=1);

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use Psr\Http\Message\RequestInterface;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Provider\Epay;
use Yansongda\Pay\Rocket;
use function Yansongda\Pay\get_epay_config;

abstract class GeneralPlugin implements PluginInterface
{
    /**
     * @param Rocket $rocket
     * @param Closure $next
     * @return Rocket
     */
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        Logger::debug('[epay][GeneralPlugin] 通用插件开始装载', ['rocket' => $rocket]);

        $rocket->setRadar($this->getRequest($rocket));
        $this->doSomething($rocket);

        Logger::info('[epay][GeneralPlugin] 通用插件装载完毕', ['rocket' => $rocket]);

        return $next($rocket);
    }

    /**
     * @param Rocket $rocket
     * @return RequestInterface
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function getRequest(Rocket $rocket): RequestInterface
    {
        return new Request(
            $this->getMethod(),
            $this->getUrl($rocket),
            $this->getHeaders(),
        );
    }

    /**
     * @return string
     */
    protected function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @param Rocket $rocket
     * @return string
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    protected function getUrl(Rocket $rocket): string
    {
        $url = $this->getUri($rocket);

        if (str_starts_with($url, 'http')) {
            return $url;
        }

        $config = get_epay_config($rocket->getParams());

        return $config['pay_url'];
    }

    /**
     * @return string[]
     */
    protected function getHeaders(): array
    {
        return [
            'User-Agent' => 'yansongda/pay-v3',
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
        ];
    }

    abstract protected function doSomething(Rocket $rocket): void;

    abstract protected function getUri(Rocket $rocket): string;

}