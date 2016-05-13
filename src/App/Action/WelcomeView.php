<?php

namespace App\Action;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Stratigility\MiddlewareInterface;
use Zend\Diactoros\Stream;

class WelcomeView
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
        $session = $request->getAttribute('session');
        $html = $this->templateRenderer->render('app::welcome');
        $stream = new Stream('php://memory', 'w+b');
        $stream->write($html);
        return $response
            ->withBody($stream)
            ->withHeader('Content-Type', 'text/html');
    }
}
