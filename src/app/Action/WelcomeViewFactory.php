<?php

namespace App\Action;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class WelcomeViewFactory
{
    /**
    *
    */
    public function __invoke(ContainerInterface $container)
    {
        $templateRenderer = $container->get(TemplateRendererInterface::class);
        return new WelcomeView($templateRenderer);
    }
}
