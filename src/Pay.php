<?php

declare(strict_types=1);

namespace Yansongda\Pay;

use Closure;
use Illuminate\Container\Container as LaravelContainer;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;
use Yansongda\Pay\Contract\DirectionInterface;
use Yansongda\Pay\Contract\PackerInterface;
use Yansongda\Pay\Contract\ServiceProviderInterface;
use Yansongda\Pay\Direction\CollectionDirection;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\ContainerNotFoundException;
use Yansongda\Pay\Exception\ServiceNotFoundException;
use Yansongda\Pay\Packer\JsonPacker;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Epay;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Provider\Wechat;
use Yansongda\Pay\Service\AlipayServiceProvider;
use Yansongda\Pay\Service\ConfigServiceProvider;
use Yansongda\Pay\Service\ContainerServiceProvider;
use Yansongda\Pay\Service\EpayServiceProvider;
use Yansongda\Pay\Service\EventServiceProvider;
use Yansongda\Pay\Service\HttpServiceProvider;
use Yansongda\Pay\Service\LoggerServiceProvider;
use Yansongda\Pay\Service\UnipayServiceProvider;
use Yansongda\Pay\Service\WechatServiceProvider;

/**
 * @method static Alipay alipay(array $config = [], $container = null)
 * @method static Wechat wechat(array $config = [], $container = null)
 * @method static Unipay unipay(array $config = [], $container = null)
 * @method static Epay epay(array $config = [], $container = null)
 */
class Pay
{
    /**
     * 正常模式.
     */
    public const MODE_NORMAL = 0;

    /**
     * 沙箱模式.
     */
    public const MODE_SANDBOX = 1;

    /**
     * 服务商模式.
     */
    public const MODE_SERVICE = 2;

    /**
     * @var string[]
     */
    protected array $service = [
        AlipayServiceProvider::class,
        WechatServiceProvider::class,
        UnipayServiceProvider::class,
        EpayServiceProvider::class,
    ];

    /**
     * @var string[]
     */
    private array $coreService = [
        ContainerServiceProvider::class,
        ConfigServiceProvider::class,
        LoggerServiceProvider::class,
        EventServiceProvider::class,
        HttpServiceProvider::class,
    ];

    private static Closure|null|ContainerInterface $container = null;

    /**
     * @throws ContainerException
     */
    private function __construct(array $config, ContainerInterface|Closure $container = null)
    {
        $this->registerServices($config, $container);

        Pay::set(DirectionInterface::class, CollectionDirection::class);
        Pay::set(PackerInterface::class, JsonPacker::class);
    }

    /**
     * @return mixed
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public static function __callStatic(string $service, array $config)
    {
        if (!empty($config)) {
            self::config(...$config);
        }

        return self::get($service);
    }

    /**
     * @throws ContainerException
     */
    public static function config(array $config = [], ContainerInterface|Closure $container = null): bool
    {
        if (self::hasContainer() && !($config['_force'] ?? false)) {
            return false;
        }

        new self($config, $container);

        return true;
    }

    /**
     * @codeCoverageIgnore
     *
     * @throws ContainerException
     */
    public static function set(string $name, mixed $value): void
    {
        try {
            $container = Pay::getContainer();

            if ($container instanceof LaravelContainer) {
                $container->singleton($name, $value instanceof Closure ? $value : static fn () => $value);

                return;
            }

            if (method_exists($container, 'set')) {
                $container->set(...func_get_args());

                return;
            }
        } catch (ContainerNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ContainerException($e->getMessage());
        }

        throw new ContainerException('Current container does NOT support `set` method');
    }

    /**
     * @codeCoverageIgnore
     *
     * @throws ContainerException
     */
    public static function make(string $service, array $parameters = []): mixed
    {
        try {
            $container = Pay::getContainer();

            if (method_exists($container, 'make')) {
                return $container->make(...func_get_args());
            }
        } catch (ContainerNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ContainerException($e->getMessage());
        }

        $parameters = array_values($parameters);

        return new $service(...$parameters);
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ContainerException
     */
    public static function get(string $service): mixed
    {
        try {
            return Pay::getContainer()->get($service);
        } catch (NotFoundExceptionInterface $e) {
            throw new ServiceNotFoundException($e->getMessage());
        } catch (ContainerNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * @throws ContainerNotFoundException
     */
    public static function has(string $service): bool
    {
        return Pay::getContainer()->has($service);
    }

    public static function setContainer(ContainerInterface|Closure|null $container): void
    {
        self::$container = $container;
    }

    /**
     * @throws ContainerNotFoundException
     */
    public static function getContainer(): ContainerInterface
    {
        if (self::$container instanceof ContainerInterface) {
            return self::$container;
        }

        if (self::$container instanceof Closure) {
            return (self::$container)();
        }

        throw new ContainerNotFoundException('`getContainer()` failed! Maybe you should `setContainer()` first', Exception\Exception::CONTAINER_NOT_FOUND);
    }

    public static function hasContainer(): bool
    {
        return self::$container instanceof ContainerInterface || self::$container instanceof Closure;
    }

    public static function clear(): void
    {
        self::$container = null;
    }

    /**
     * @throws ContainerException
     */
    public static function registerService(string $service, mixed $data): void
    {
        $var = new $service();

        if ($var instanceof ServiceProviderInterface) {
            $var->register($data);
        }
    }

    /**
     * @throws ContainerException
     */
    private function registerServices(array $config, ContainerInterface|Closure $container = null): void
    {
        foreach (array_merge($this->coreService, $this->service) as $service) {
            self::registerService($service, ContainerServiceProvider::class == $service ? $container : $config);
        }
    }
}
