<?php

namespace IndieHD\AudioManipulator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Monolog\Logger;

use \getID3;
use \getid3_writetags;

use IndieHD\AudioManipulator\Flac\FlacConverter;
use IndieHD\AudioManipulator\Flac\FlacManipulatorCreator;
use IndieHD\AudioManipulator\Flac\FlacTagger;
use IndieHD\AudioManipulator\Mp3\Mp3ManipulatorCreator;
use IndieHD\AudioManipulator\Mp3\Mp3Converter;
use IndieHD\AudioManipulator\Mp3\Mp3Tagger;
use IndieHD\AudioManipulator\Validation\Validator;
use IndieHD\AudioManipulator\Tagging\Tagger;
use IndieHD\AudioManipulator\Transcoding\Transcoder;
use IndieHD\AudioManipulator\Processing\Process;
use IndieHD\AudioManipulator\MediaParsing\MediaParser;

class Container
{
    public function __construct()
    {
        $containerBuilder = new ContainerBuilder();

        // Logger.

        $containerBuilder->setParameter('logger.monolog', 'general');

        $containerBuilder->register('logger', Logger::class)
            ->addArgument('%logger.monolog%');

        // Validator.

        $containerBuilder->setParameter('validator.media_parser', new MediaParser);

        $containerBuilder->register('validator', Validator::class)
            ->addArgument('%validator.media_parser%');

        // Tagger.

        $containerBuilder->setParameter('tagger.getid3', new getID3);
        $containerBuilder->setParameter('tagger.getid3_tag_writer', new getid3_writetags);
        $containerBuilder->setParameter('tagger.process', new Process());
        $containerBuilder->setParameter('tagger.logger', $containerBuilder->get('logger'));

        $containerBuilder->register('tagger', Tagger::class)
            ->addArgument('%tagger.getid3%')
            ->addArgument('%tagger.getid3_tag_writer%')
            ->addArgument('%tagger.process%')
            ->addArgument('%tagger.logger%');

        // Transcoder.

        $containerBuilder->setParameter('transcoder.validator', $containerBuilder->get('validator'));
        $containerBuilder->setParameter('transcoder.tagger', $containerBuilder->get('tagger'));
        $containerBuilder->setParameter('transcoder.process', new Process());
        $containerBuilder->setParameter('transcoder.logger', $containerBuilder->get('logger'));

        $containerBuilder
            ->register('transcoder', Transcoder::class)
            ->addArgument('%transcoder.validator%')
            ->addArgument('%transcoder.tagger%')
            ->addArgument('%transcoder.process%')
            ->addArgument('%transcoder.logger%');

        // FLAC Converter.

        $containerBuilder->setParameter('flac_converter.validator', $containerBuilder->get('validator'));
        $containerBuilder->setParameter('flac_converter.process', new Process());
        $containerBuilder->setParameter('flac_converter.logger', $containerBuilder->get('logger'));

        $containerBuilder
            ->register('flac_converter', FlacConverter::class)
            ->addArgument('%flac_converter.validator%')
            ->addArgument('%flac_converter.process%')
            ->addArgument('%flac_converter.logger%');

        // FLAC Manipulator.

        $containerBuilder->setParameter('flac_manipulator_creator.converter', $containerBuilder->get('flac_converter'));
        $containerBuilder->setParameter('flac_manipulator_creator.tagger', new FlacTagger());

        $containerBuilder
            ->register('flac_manipulator_creator', FlacManipulatorCreator::class)
            ->addArgument('%flac_manipulator_creator.converter%')
            ->addArgument('%flac_manipulator_creator.tagger%');

        // MP3 Converter.

        $containerBuilder->setParameter('mp3_converter.validator', $containerBuilder->get('validator'));
        $containerBuilder->setParameter('mp3_converter.process', new Process());
        $containerBuilder->setParameter('mp3_converter.logger', $containerBuilder->get('logger'));

        $containerBuilder
            ->register('mp3_converter', Mp3Converter::class)
            ->addArgument('%mp3_converter.validator%')
            ->addArgument('%mp3_converter.process%')
            ->addArgument('%mp3_converter.logger%');

        // MP3 Manipulator.

        $containerBuilder->setParameter('mp3_manipulator_creator.converter', $containerBuilder->get('mp3_converter'));
        $containerBuilder->setParameter('mp3_manipulator_creator.tagger', new Mp3Tagger());

        $containerBuilder
            ->register('mp3_manipulator_creator', Mp3ManipulatorCreator::class)
            ->addArgument('%mp3_manipulator_creator.converter%')
            ->addArgument('%mp3_manipulator_creator.tagger%');

        //

        $this->builder = $containerBuilder;
    }
}
