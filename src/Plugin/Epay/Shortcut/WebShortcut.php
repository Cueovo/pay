<?php

namespace Yansongda\Pay\Plugin\Epay\Shortcut;

use Yansongda\Pay\Contract\ShortcutInterface;
use Yansongda\Pay\Plugin\Epay\HtmlResponsePlugin;
use Yansongda\Pay\Plugin\Epay\Trade\PagePayPlugin;
use Yansongda\Pay\Rocket;

class WebShortcut implements ShortcutInterface
{
    public function getPlugins(array $params): array
    {
        return [
            PagePayPlugin::class,
            HtmlResponsePlugin::class,
        ];
    }
}