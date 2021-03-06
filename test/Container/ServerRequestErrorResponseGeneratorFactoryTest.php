<?php

/**
 * @see       https://github.com/mezzio/mezzio for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Container;

use Closure;
use Mezzio\Container\ServerRequestErrorResponseGeneratorFactory;
use Mezzio\Response\ServerRequestErrorResponseGenerator;
use Mezzio\Template\TemplateRendererInterface;
use MezzioTest\InMemoryContainer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class ServerRequestErrorResponseGeneratorFactoryTest extends TestCase
{
    public function testFactoryOnlyRequiresResponseService() : void
    {
        $container = new InMemoryContainer();
        $factory = new ServerRequestErrorResponseGeneratorFactory();

        $this->expectException(RuntimeException::class);
        $factory($container);
    }

    public function testFactoryCreatesGeneratorWhenOnlyResponseServiceIsPresent() : void
    {
        $container = new InMemoryContainer();

        $responseFactory = function () {
        };
        $container->set(ResponseInterface::class, $responseFactory);

        $factory = new ServerRequestErrorResponseGeneratorFactory();

        $generator = $factory($container);

        self::assertEquals(new ServerRequestErrorResponseGenerator($responseFactory), $generator);
    }

    public function testFactoryCreatesGeneratorUsingConfiguredServices() : void
    {
        $config = [
            'debug' => true,
            'mezzio' => [
                'error_handler' => [
                    'template_error' => 'some::template',
                ],
            ],
        ];
        $renderer = $this->createMock(TemplateRendererInterface::class);

        $container = new InMemoryContainer();
        $container->set('config', $config);
        $container->set(TemplateRendererInterface::class, $renderer);

        $responseFactory = function () {
        };
        $container->set(ResponseInterface::class, $responseFactory);

        $factory = new ServerRequestErrorResponseGeneratorFactory();

        $generator = $factory($container);

        self::assertEquals(
            new ServerRequestErrorResponseGenerator(
                $responseFactory,
                true,
                $renderer,
                $config['mezzio']['error_handler']['template_error']
            ),
            $generator
        );
    }
}
