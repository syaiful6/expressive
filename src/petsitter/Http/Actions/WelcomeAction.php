<?php

namespace Petsitter\Http\Actions;

use Zend\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class WelcomeAction
{
    /**
     * @var Zend\Expressive\Template\TemplateRendererInterface
     */
    protected $templateRenderer;

    /**
    *
    */
    public function __construct(TemplateRendererInterface $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    /**
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $html = $this->templateRenderer->render('app::welcome');
        $stream = new Stream('php://memory', 'w+b');
        $stream->write($html);
        return $response
            ->withBody($stream)
            ->withHeader('Content-Type', 'text/html');
    }
}
