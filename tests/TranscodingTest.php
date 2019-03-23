<?php

namespace IndieHD\AudioManipulator;

#require_once __DIR__.'/../vendor/autoload.php';

use IndieHD\AudioManipulator\Validation\Validator;
use IndieHD\AudioManipulator\Tagging\Tagger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
#use IndieHD\AudioManipulator\ContainerBuilder;
use IndieHD\AudioManipulator\Transcoding\Transcoder;
use IndieHD\AudioManipulator\Process;

use \getID3;
use \getid3_writetags;

class TranscodingTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        // TODO Here's a basic example of how to use Symfony's DI implementation
        // for this library. This should all be refactored and moved into a
        // more suitable location.
        
        $containerBuilder = new ContainerBuilder();
    
        $containerBuilder->setParameter('tagger.getid3', new getID3);
        $containerBuilder->setParameter('tagger.getid3_tag_writer', new getid3_writetags);
        
        $containerBuilder->register('tagger', Tagger::class)
            ->addArgument('%tagger.getid3%')
            ->addArgument('%tagger.getid3_tag_writer%');
        
        $containerBuilder->setParameter('transcoder.validator', new Validator());
        $containerBuilder->setParameter('transcoder.tagger', $containerBuilder->get('tagger'));
        $containerBuilder->setParameter('transcoder.process', new Process());
        
        $containerBuilder
            ->register('transcoder', Transcoder::class)
            ->addArgument('%transcoder.validator%')
            ->addArgument('%transcoder.tagger%')
            ->addArgument('%transcoder.process%');
        
        $this->transcoder = $containerBuilder->get('transcoder');
    }

    public function testTranscodingFlacToMp3Succeeds()
    {
        $testDir = __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;

        $this->assertTrue($this->transcoder->transcode(
            $testDir . 'foo.flac',
            $testDir . 'foo.mp3'
        )['result']);
    }
}
