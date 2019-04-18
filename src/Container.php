<?php

namespace IndieHD\AudioManipulator;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Monolog\Logger;

use \getID3;
use \getid3_writetags;

use IndieHD\FilenameSanitizer\FilenameSanitizer;

use IndieHD\AudioManipulator\Flac\FlacManipulatorCreator;
use IndieHD\AudioManipulator\Flac\FlacConverter;
use IndieHD\AudioManipulator\Flac\FlacTagger;
use IndieHD\AudioManipulator\Wav\WavManipulatorCreator;
use IndieHD\AudioManipulator\Wav\WavConverter;
use IndieHD\AudioManipulator\Mp3\Mp3ManipulatorCreator;
use IndieHD\AudioManipulator\Mp3\Mp3Converter;
use IndieHD\AudioManipulator\Mp3\Mp3Tagger;
use IndieHD\AudioManipulator\Validation\Validator;
use IndieHD\AudioManipulator\Processing\Process;
use IndieHD\AudioManipulator\MediaParsing\MediaParser;
use IndieHD\AudioManipulator\Effects\Effects;
use IndieHD\AudioManipulator\Flac\FlacEffects;
use IndieHD\AudioManipulator\Alac\AlacEffects;
use IndieHD\AudioManipulator\CliCommand\SoxCommand;
use IndieHD\AudioManipulator\CliCommand\FfmpegCommand;
use IndieHD\AudioManipulator\CliCommand\MetaflacCommand;

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

        // ALAC Effects.

        $containerBuilder->setParameter('alac_effects.cli_command', new FfmpegCommand());

        $containerBuilder
            ->register('alac_effects', AlacEffects::class)
            ->addArgument('%alac_effects.cli_command%');

        // FLAC Effects.

        $containerBuilder->setParameter('flac_effects.cli_command', new SoxCommand());

        $containerBuilder
            ->register('flac_effects', FlacEffects::class)
            ->addArgument('%flac_effects.cli_command%');

        $containerBuilder->register('effects', Effects::class)
            ->addMethodCall('setFlac', [new Reference('flac_effects')])
            ->addMethodCall('setAlac', [new Reference('alac_effects')]);

        // FLAC Converter.

        $containerBuilder->setParameter('flac_converter.validator', $containerBuilder->get('validator'));
        $containerBuilder->setParameter('flac_converter.process', new Process());
        $containerBuilder->setParameter('flac_converter.logger', $containerBuilder->get('logger'));
        $containerBuilder->setParameter('flac_converter.sox', new SoxCommand());
        $containerBuilder->setParameter('flac_converter.ffmpeg', new FfmpegCommand());

        $containerBuilder
            ->register('flac_converter', FlacConverter::class)
            ->addArgument('%flac_converter.validator%')
            ->addArgument('%flac_converter.process%')
            ->addArgument('%flac_converter.logger%')
            ->addArgument('%flac_converter.sox%')
            ->addArgument('%flac_converter.ffmpeg%');

        // FLAC Tagger.

        $containerBuilder->setParameter('flac_tagger.getid3', new getID3());
        $containerBuilder->setParameter('flac_tagger.getid3_tag_writer', new getid3_writetags);
        $containerBuilder->setParameter('flac_tagger.process', new Process());
        $containerBuilder->setParameter('flac_tagger.logger', $containerBuilder->get('logger'));
        $containerBuilder->setParameter('flac_tagger.filename_sanitizer', new FilenameSanitizer());
        $containerBuilder->setParameter('flac_tagger.cli_command', new MetaflacCommand());
        $containerBuilder->setParameter('flac_tagger.validator', $containerBuilder->get('validator'));

        $containerBuilder->register('flac_tagger', FlacTagger::class)
            ->addArgument('%flac_tagger.getid3%')
            ->addArgument('%flac_tagger.getid3_tag_writer%')
            ->addArgument('%flac_tagger.process%')
            ->addArgument('%flac_tagger.logger%')
            ->addArgument('%flac_tagger.filename_sanitizer%')
            ->addArgument('%flac_tagger.cli_command%')
            ->addArgument('%flac_tagger.validator%');

        // FLAC Manipulator.

        $containerBuilder->setParameter('flac_manipulator_creator.converter', $containerBuilder->get('flac_converter'));
        $containerBuilder->setParameter('flac_manipulator_creator.tagger', $containerBuilder->get('flac_tagger'));

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

        // MP3 Tagger.

        $containerBuilder->setParameter('mp3_tagger.validator', $containerBuilder->get('validator'));
        $containerBuilder->setParameter('mp3_tagger.getid3', new getID3());
        $containerBuilder->setParameter('mp3_tagger.getid3_tag_writer', new getid3_writetags);
        $containerBuilder->setParameter('mp3_tagger.process', new Process());
        $containerBuilder->setParameter('mp3_tagger.logger', $containerBuilder->get('logger'));
        $containerBuilder->setParameter('mp3_tagger.filename_sanitizer', new FilenameSanitizer());

        $containerBuilder->register('mp3_tagger', Mp3Tagger::class)
            ->addArgument('%mp3_tagger.validator%')
            ->addArgument('%mp3_tagger.getid3%')
            ->addArgument('%mp3_tagger.getid3_tag_writer%')
            ->addArgument('%mp3_tagger.process%')
            ->addArgument('%mp3_tagger.logger%')
            ->addArgument('%mp3_tagger.filename_sanitizer%');

        // MP3 Manipulator.

        $containerBuilder->setParameter('mp3_manipulator_creator.tagger', $containerBuilder->get('mp3_tagger'));

        $containerBuilder
            ->register('mp3_manipulator_creator', Mp3ManipulatorCreator::class)
            ->addArgument('%mp3_manipulator_creator.tagger%');

        // WAV Converter.

        $containerBuilder->setParameter('wav_converter.validator', $containerBuilder->get('validator'));
        $containerBuilder->setParameter('wav_converter.process', new Process());
        $containerBuilder->setParameter('wav_converter.logger', $containerBuilder->get('logger'));

        $containerBuilder
            ->register('wav_converter', WavConverter::class)
            ->addArgument('%wav_converter.validator%')
            ->addArgument('%wav_converter.process%')
            ->addArgument('%wav_converter.logger%');

        // WAV Manipulator.

        $containerBuilder->setParameter('wav_manipulator_creator.converter', $containerBuilder->get('wav_converter'));

        $containerBuilder
            ->register('wav_manipulator_creator', WavManipulatorCreator::class)
            ->addArgument('%wav_manipulator_creator.converter%');

        //

        $this->builder = $containerBuilder;
    }
}
