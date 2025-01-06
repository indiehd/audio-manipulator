<?php

namespace IndieHD\AudioManipulator\Container;

use Dotenv\Dotenv;
use getID3;
use IndieHD\AudioManipulator\Alac\AlacManipulatorCreator;
use IndieHD\AudioManipulator\Alac\AlacTagger;
use IndieHD\AudioManipulator\Alac\AlacTagVerifier;
use IndieHD\AudioManipulator\CliCommand\AtomicParsleyCommand;
use IndieHD\AudioManipulator\CliCommand\FfmpegCommand;
use IndieHD\AudioManipulator\CliCommand\MetaflacCommand;
use IndieHD\AudioManipulator\CliCommand\Mid3v2Command;
use IndieHD\AudioManipulator\CliCommand\SoxCommand;
use IndieHD\AudioManipulator\Flac\FlacConverter;
use IndieHD\AudioManipulator\Flac\FlacManipulatorCreator;
use IndieHD\AudioManipulator\Flac\FlacTagger;
use IndieHD\AudioManipulator\Flac\FlacTagVerifier;
use IndieHD\AudioManipulator\Logging\Logger;
use IndieHD\AudioManipulator\MediaParsing\MediaParser;
use IndieHD\AudioManipulator\Mp3\Mp3Converter;
use IndieHD\AudioManipulator\Mp3\Mp3ManipulatorCreator;
use IndieHD\AudioManipulator\Mp3\Mp3Tagger;
use IndieHD\AudioManipulator\Mp3\Mp3TagVerifier;
use IndieHD\AudioManipulator\Processing\Process;
use IndieHD\AudioManipulator\Validation\Validator;
use IndieHD\AudioManipulator\Wav\WavConverter;
use IndieHD\AudioManipulator\Wav\WavManipulatorCreator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Container
{
    public ContainerBuilder $builder;

    public function __construct()
    {
        // Configuration.

        if (getenv('APP_ENV') === 'development') {
            Dotenv::create([
                __DIR__.DIRECTORY_SEPARATOR
                .'..'.DIRECTORY_SEPARATOR
                .'..'.DIRECTORY_SEPARATOR,
            ])->load();
        }

        // Container.

        $containerBuilder = new ContainerBuilder();

        // Validator.

        $containerBuilder->register('validator', Validator::class)
            ->addArgument(new MediaParser());

        // Tag Verifier.

        $containerBuilder->register('alac.tag_verifier', AlacTagVerifier::class)
            ->addArgument(new getID3());

        $containerBuilder->register('flac.tag_verifier', FlacTagVerifier::class)
            ->addArgument(new getID3());

        $containerBuilder->register('mp3.tag_verifier', Mp3TagVerifier::class)
            ->addArgument(new getID3());

        // ALAC Logger.

        $containerBuilder->register('alac.tagger.logger', MonologLogger::class)
            ->addArgument('alac-tagger');

        $containerBuilder->register('alac.tagger.handler', StreamHandler::class)
            ->addArgument(
                __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.getenv('ALAC_TAGGER_LOG')
            );

        $containerBuilder->register('logger.tagger.alac', Logger::class)
            ->addArgument(new Reference('alac.tagger.logger'))
            ->addArgument(new Reference('alac.tagger.handler'));

        // ALAC Tagger.

        $containerBuilder->register('alac_tagger', AlacTagger::class)
            ->addArgument(new Reference('alac.tag_verifier'))
            ->addArgument(new Process())
            ->addArgument(new Reference('logger.tagger.alac'))
            ->addArgument(new AtomicParsleyCommand())
            ->addArgument(new Reference('validator'));

        // ALAC Manipulator.

        $containerBuilder
            ->register('alac_manipulator_creator', AlacManipulatorCreator::class)
            ->addArgument(new Reference('alac_tagger'));

        // FLAC Loggers.

        // Converter Logger.

        $containerBuilder->register('flac.converter.logger', MonologLogger::class)
            ->addArgument('flac-converter');

        $containerBuilder->register('flac.converter.handler', StreamHandler::class)
            ->addArgument(
                __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.getenv('FLAC_CONVERTER_LOG')
            );

        $containerBuilder->register('logger.converter.flac', Logger::class)
            ->addArgument(new Reference('flac.converter.logger'))
            ->addArgument(new Reference('flac.converter.handler'));

        // Tagger Logger.

        $containerBuilder->register('flac.tagger.logger', MonologLogger::class)
            ->addArgument('flac-tagger');

        $containerBuilder->register('flac.tagger.handler', StreamHandler::class)
            ->addArgument(
                __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.getenv('FLAC_TAGGER_LOG')
            );

        $containerBuilder->register('logger.tagger.flac', Logger::class)
            ->addArgument(new Reference('flac.tagger.logger'))
            ->addArgument(new Reference('flac.tagger.handler'));

        // FLAC Converter.

        $containerBuilder
            ->register('flac_converter', FlacConverter::class)
            ->addArgument(new Reference('validator'))
            ->addArgument(new Process())
            ->addArgument(new Reference('logger.converter.flac'))
            ->addArgument(new SoxCommand())
            ->addArgument(new FfmpegCommand());

        // FLAC Tagger.

        $containerBuilder->register('flac_tagger', FlacTagger::class)
            ->addArgument(new Reference('flac.tag_verifier'))
            ->addArgument(new Process())
            ->addArgument(new Reference('logger.tagger.flac'))
            ->addArgument(new MetaflacCommand())
            ->addArgument(new Reference('validator'));

        // FLAC Manipulator.

        $containerBuilder
            ->register('flac_manipulator_creator', FlacManipulatorCreator::class)
            ->addArgument(new Reference('flac_converter'))
            ->addArgument(new Reference('flac_tagger'));

        // MP3 Logger.

        // TODO Implement this.

        /*
        $containerBuilder->register('mp3.converter.logger', MonologLogger::class)
            ->addArgument('mp3-converter');

        $containerBuilder->register('mp3.converter.handler', StreamHandler::class)
            ->addArgument(
                __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . getenv('MP3_CONVERTER_LOG')
            );

        $containerBuilder->register('logger.converter.mp3', Logger::class)
            ->addArgument(new Reference('mp3.converter.logger'));
            ->addArgument(new Reference('mp3.converter.handler'));
        */

        $containerBuilder->register('mp3.tagger.logger', MonologLogger::class)
            ->addArgument('mp3-tagger');

        $containerBuilder->register('mp3.tagger.handler', StreamHandler::class)
            ->addArgument(
                __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.getenv('MP3_TAGGER_LOG')
            );

        $containerBuilder->register('logger.tagger.mp3', Logger::class)
            ->addArgument(new Reference('mp3.tagger.logger'))
            ->addArgument(new Reference('mp3.tagger.handler'));

        // MP3 Converter.

        // TODO Implement this.

        /*
        $containerBuilder->setParameter('mp3_converter.validator', new Reference('validator'));
        $containerBuilder->setParameter('mp3_converter.process', new Process());
        $containerBuilder->setParameter('mp3_converter.logger', new Reference('logger.converter.mp3'));

        $containerBuilder
            ->register('mp3_converter', Mp3Converter::class)
            ->addArgument('%mp3_converter.validator%')
            ->addArgument('%mp3_converter.process%')
            ->addArgument('%mp3_converter.logger%')
            ->addArgument('%mp3_converter.handler%');
        */

        // MP3 Tagger.

        $containerBuilder->register('mp3_tagger', Mp3Tagger::class)
            ->addArgument(new Reference('mp3.tag_verifier'))
            ->addArgument(new Process())
            ->addArgument(new Reference('logger.tagger.mp3'))
            ->addArgument(new Mid3v2Command());

        // MP3 Manipulator.

        $containerBuilder
            ->register('mp3_manipulator_creator', Mp3ManipulatorCreator::class)
            ->addArgument(new Reference('mp3_tagger'));

        // WAV Logger.

        $containerBuilder->register('wav.converter.logger', MonologLogger::class)
            ->addArgument('wav-converter');

        $containerBuilder->register('wav.converter.handler', StreamHandler::class)
            ->addArgument(
                __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.getenv('WAV_CONVERTER_LOG')
            );

        $containerBuilder->register('logger.converter.wav', Logger::class)
            ->addArgument(new Reference('wav.converter.logger'))
            ->addArgument(new Reference('wav.converter.handler'));

        // WAV Converter.

        $containerBuilder
            ->register('wav_converter', WavConverter::class)
            ->addArgument(new Reference('validator'))
            ->addArgument(new Process())
            ->addArgument(new Reference('logger.converter.wav'))
            ->addArgument(new SoxCommand())
            ->addArgument(new FfmpegCommand());

        // WAV Manipulator.

        $containerBuilder
            ->register('wav_manipulator_creator', WavManipulatorCreator::class)
            ->addArgument(new Reference('wav_converter'));

        //

        $this->builder = $containerBuilder;
    }
}
