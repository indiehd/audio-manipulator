<?php

namespace IndieHD\AudioManipulator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Monolog\Logger;

use \getID3;
use \getid3_writetags;

use IndieHD\AudioManipulator\Validation\Validator;
use IndieHD\AudioManipulator\Tagging\Tagger;
use IndieHD\AudioManipulator\Transcoding\Transcoder;
use IndieHD\AudioManipulator\Process;

class Container
{
    public function __construct()
    {
        $containerBuilder = new ContainerBuilder();
    
        $containerBuilder->setParameter('logger.monolog', 'general');
    
        $containerBuilder->register('logger', Logger::class)
            ->addArgument('%logger.monolog%');
        
        $containerBuilder->setParameter('tagger.getid3', new getID3);
        $containerBuilder->setParameter('tagger.getid3_tag_writer', new getid3_writetags);
        $containerBuilder->setParameter('tagger.process', new Process());
        $containerBuilder->setParameter('tagger.logger', $containerBuilder->get('logger'));
    
        $containerBuilder->register('tagger', Tagger::class)
            ->addArgument('%tagger.getid3%')
            ->addArgument('%tagger.getid3_tag_writer%')
            ->addArgument('%tagger.process%')
            ->addArgument('%tagger.logger%');
    
        $containerBuilder->setParameter('transcoder.validator', new Validator());
        $containerBuilder->setParameter('transcoder.tagger', $containerBuilder->get('tagger'));
        $containerBuilder->setParameter('transcoder.process', new Process());
        $containerBuilder->setParameter('transcoder.logger', $containerBuilder->get('logger'));
    
        $containerBuilder
            ->register('transcoder', Transcoder::class)
            ->addArgument('%transcoder.validator%')
            ->addArgument('%transcoder.tagger%')
            ->addArgument('%transcoder.process%')
            ->addArgument('%transcoder.logger%');
    
        $this->builder = $containerBuilder;
    }
}
