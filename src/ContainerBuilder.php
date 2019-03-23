<?php

namespace IndieHD\AudioManipulator;

use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;

class ContainerBuilder
{
    public function __construct()
    {
        $containerBuilder = new SymfonyContainerBuilder();
    
        $containerBuilder->setParameter('transcoder.process', 'symfony');
        $containerBuilder
            ->register('transcoder', 'Transcoder')
            ->addArgument('%transcoder.process%');
    
        #$containerBuilder
        #    ->register('newsletter_manager', 'NewsletterManager')
        #    ->addArgument(new Reference('mailer'));
    }
}
