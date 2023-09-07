<?php

declare(strict_types=1);

namespace Yansongda\Pay\Provider;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yansongda\Pay\Event;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Plugin\Epay\CallbackPlugin;
use Yansongda\Pay\Plugin\Epay\LaunchPlugin;
use Yansongda\Pay\Plugin\Epay\PreparePlugin;
use Yansongda\Pay\Plugin\Epay\RadarSignPlugin;
use Yansongda\Pay\Plugin\ParserPlugin;
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

    public function callback(null|array|ServerRequestInterface $contents = null, ?array $params = null): Collection
    {
        $request = $this->getCallbackParams($contents);
        Event::dispatch(new Event\CallbackReceived('epay', $request->all(), $params, null));

        return $this->pay(
            [CallbackPlugin::class],
            $request->merge($params)->all()
        );
    }

    public function success(): ResponseInterface
    {
        return new Response(200, [], 'success');
    }

    public function mergeCommonPlugins(array $plugins): array
    {
        return array_merge(
            [PreparePlugin::class],
            $plugins,
            [RadarSignPlugin::class],
            [LaunchPlugin::class, ParserPlugin::class],
        );
    }

    protected function getCallbackParams(null|array|ServerRequestInterface $contents = null): Collection
    {
        if (is_array($contents)) {
            return Collection::wrap($contents);
        }

        if ($contents instanceof ServerRequestInterface) {
            return Collection::wrap($contents->getParsedBody());
        }

        $request = ServerRequest::fromGlobals();

        return Collection::wrap($request->getParsedBody());
    }
}