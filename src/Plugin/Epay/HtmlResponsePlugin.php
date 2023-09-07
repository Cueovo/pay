<?php

namespace Yansongda\Pay\Plugin\Epay;

use Closure;
use GuzzleHttp\Psr7\Response;
use Yansongda\Pay\Contract\PluginInterface;
use Yansongda\Pay\Logger;
use Yansongda\Pay\Rocket;
use Yansongda\Supports\Collection;
class HtmlResponsePlugin implements PluginInterface
{
    public function assembly(Rocket $rocket, Closure $next): Rocket
    {
        /* @var Rocket $rocket */
        $rocket = $next($rocket);

        Logger::debug('[epay][HtmlResponsePlugin] 插件开始装载', ['rocket' => $rocket]);

        $radar = $rocket->getRadar();
        $response = $this->buildHtml($radar->getUri()->__toString(), $rocket->getPayload(), $rocket->getParams());

        $rocket->setDestination($response);

        Logger::info('[epay][HtmlResponsePlugin] 插件装载完毕', ['rocket' => $rocket]);

        return $rocket;
    }

    protected function buildHtml(string $endpoint, Collection $payload, array $params): Response
    {
        $payload = $payload->merge($params)->toArray();

        $sHtml = "<form id='pay_form' name='pay_form' action='" . $endpoint . "' method='POST'>";
        foreach ($payload as $key => $val) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        $sHtml .= "<div class=\"col-lg-8 col-md-12 col-lg-offset-2 text-center\"><div class=\"panel panel-info\"><div class=\"panel-heading\"><b>跳转支付</b></div><div class=\"panel-body\"style=\"padding-bottom:15px;\"><div style=\"padding:30px 0;\"><i class=\"fa fa-check text-success\"style=\"font-size:40px;\"></i><h4>正在前往支付页面~~</h4></div></div></div></div></form>";
        $sHtml .= "<script>document.forms['pay_form'].submit();</script>";

        return new Response(200, [], $sHtml);
    }
}