<?php

declare(strict_types=1);

namespace Stairs;

use DI\Container;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stairs\Dispatchers\QueueMiddlewareDispatcher;
use Stairs\Interfaces\MiddlewareDispatcherInterface;

class Application
{
    protected Container $container;

    protected ServerRequestInterface $request;

    protected ResponseFactoryInterface $responseFactory;

    protected MiddlewareDispatcherInterface $middlewareDispatcher;

    public function __construct(Container $container)
    {
        if (!$container->has(ServerRequestInterface::class)) {
            throw new InvalidArgumentException('You must provide an instance of ServerRequestInterface.');
        } else {
            /** @var ServerRequestInterface $request */
            $request = $container->get(ServerRequestInterface::class);
        }

        if (!$container->has(ResponseFactoryInterface::class)) {
            throw new InvalidArgumentException('You must provide an instance of ResponseFactoryInterface.');
        } else {
            /** @var ResponseFactoryInterface $responseFactory */
            $responseFactory = $container->get(ResponseFactoryInterface::class);
        }

        if (!$container->has(MiddlewareDispatcherInterface::class)) {
            $middlewareDispatcher = new QueueMiddlewareDispatcher($responseFactory, $container);
        } else {
            /** @var MiddlewareDispatcherInterface $middlewareDispatcher */
            $middlewareDispatcher = $container->get(MiddlewareDispatcherInterface::class);
        }

        $this->container = $container;
        $this->request = $request;
        $this->responseFactory = $responseFactory;
        $this->middlewareDispatcher = $middlewareDispatcher;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    public function getMiddlewareDispatcher(): MiddlewareDispatcherInterface
    {
        return $this->middlewareDispatcher;
    }

    public function start(): void
    {
        $this->middlewareDispatcher->handle($this->request);
    }
}
