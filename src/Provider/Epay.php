<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Str;

/**
 * @method ResponseInterface web(array $order)      网页支付
 */
class Epay extends AbstractProvider
{
    /**
     * @throws ContainerException
     * @throws InvalidParamsException
     * @throws ServiceNotFoundException
     */
    public function __call(string $shortcut, array $params)
    {
        $plugin = '\\Yansongda\\Pay\\Plugin\\Epay\\Shortcut\\' . Str::studly($shortcut) . 'Shortcut';

        return $this->call($plugin, ...$params);
    }

    public function find(array|string $order): Collection|array
    {
        return [];
    }

    public function cancel(array|string $order): array|Collection|null
    {
        return [];
    }

    public function close(array|string $order): array|Collection|null
    {
        return [];
    }

    public function refund(array $order): Collection|array
    {
        return [];
    }

    public function callback(ServerRequestInterface|array|null $contents = null, ?array $params = null): Collection
    {
        return [];
    }

    public function success(): ResponseInterface
    {
        return [];
    }

    public function mergeCommonPlugins(array $plugins): array
    {
        return [];
    }
}