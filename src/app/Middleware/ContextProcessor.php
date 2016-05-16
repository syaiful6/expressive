<?php

namespace App\Middleware;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use function Itertools\iter;

class ContextProcessor
{
    /**
     * an array of callable
     *
     * @var processors
     */
    protected $processors;

    /**
     * the container, used to resolve processor based classes
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     *
     */
    public function __construct(
        ContainerInterface $container,
        array $processors = []
    ) {
        $this->processors = $processors;
        $this->container = $container;
    }

    /**
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $template = $this->container->get(TemplateRendererInterface::class);
        // we will spin through all processor an make add them to template
        foreach ($this->processors as $processor) {
            if (!is_callable($processor) &&
                is_string($processor) && $this->container->has($processor)) {
                $processor = $this->container->get($processor);
                if (!is_callable($processor)) {
                    throw new \RuntimeException(sprintf(
                        '%s template context processor not callable and not available on container.',
                        get_class($processor)
                    ));
                }
            } elseif (!is_callable($processor)) {
                throw new \RuntimeException(sprintf(
                    '%s template context processor not callable and not available on container.',
                    $processor
                ));
            }
            $context = $this->getIterableContext($processor, $request);
            foreach ($context as $k => $v) {
                $template->addDefaultParam(
                    TemplateRendererInterface::TEMPLATE_ALL,
                    $k,
                    $v
                );
            }
        }
        return $next($request, $response);
    }

    /**
     *
     */
    protected function getIterableContext($processor, $request)
    {
        try {
            return iter($processor($request));
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException(
                sprintf(
                    '%s processor return invalid context. Must return array or traversable',
                    is_object($processor) ? get_class($processor) : $processor
                ),
                500,
                $e
            );
        }
    }
}
