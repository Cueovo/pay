<?php

namespace Yansongda\Pay\Contract;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Supports\Collection;

interface PluginInterface
{
    /**
     * Query an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     * @param string       $type  query type
     */
    public function find($order, string $type): Collection;

    /**
     * Refund an order.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function refund(array $order): Collection;

    /**
     * Cancel an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     */
    public function cancel($order): Collection;

    /**
     * Close an order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array $order
     */
    public function close($order): Collection;

    /**
     * Verify a request.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string|array|null $content content from server
     * @param bool              $refund  is refund?
     */
    public function verify($content, bool $refund): Collection;

    /**
     * Echo success to server.
     *
     * @author yansongda <me@yansongda.cn>
     */
    public function success(): Response;
}
