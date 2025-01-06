<?php

namespace IndieHD\AudioManipulator\Tests\Flac;

use IndieHD\AudioManipulator\Flac\FlacManipulator;
use IndieHD\AudioManipulator\Flac\FlacManipulatorCreatorInterface;
use IndieHD\AudioManipulator\Processing\ProcessFailedException;
use IndieHD\AudioManipulator\Validation\InvalidAudioFileException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FlacTest extends TestCase
{
    private string $testDir;

    private string $tmpDir;

    private string $sampleFile;

    private string $tmpFile;

    public FlacManipulatorCreatorInterface $flacManipulatorCreator;

    public FlacManipulator $flacManipulator;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->testDir = __DIR__.DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR;

        $this->tmpDir = $this->testDir.'storage'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR;

        $this->sampleFile = $this->testDir.'samples'.DIRECTORY_SEPARATOR.'test.flac';

        $this->tmpFile = $this->tmpDir.DIRECTORY_SEPARATOR.'test.flac';

        copy($this->sampleFile, $this->tmpFile);

        $this->flacManipulatorCreator = \IndieHD\AudioManipulator\app()->builder
            ->get('flac_manipulator_creator');

        $this->flacManipulator = $this->flacManipulatorCreator
            ->create($this->tmpFile);
    }

    /**
     * Ensure that a FLAC file can be converted to an MP3 file.
     *
     * @return void
     */
    public function testFlacManipulatorCanConvertToMp3()
    {
        $this->assertIsArray(
            $this->flacManipulator->converter->toMp3(
                $this->flacManipulator->getFile(),
                $this->tmpDir.'foo.mp3'
            )
        );
    }

    /**
     * Ensure that a FLAC file can be converted to a WAV file.
     *
     * @return void
     */
    public function testFlacManipulatorCanConvertToWav()
    {
        $this->assertIsArray(
            $this->flacManipulator->converter->toWav(
                $this->flacManipulator->getFile(),
                $this->tmpDir.'foo.wav'
            )
        );
    }

    /**
     * Ensure that a FLAC file can be converted to an ALAC file.
     *
     * @return void
     */
    public function testFlacManipulatorCanConvertToAlac()
    {
        $this->assertIsArray(
            $this->flacManipulator->converter->toAlac(
                $this->flacManipulator->getFile(),
                $this->tmpDir.'foo.m4a'
            )
        );
    }

    public function testExceptionIsThrownWhenProcessFailsWritingToMp3()
    {
        $this->expectException(ProcessFailedException::class);

        $this->flacManipulator->converter->toMp3(
            $this->flacManipulator->getFile(),
            $this->tmpDir.'foo.bar'
        );
    }

    public function testExceptionIsThrownWhenMp3InputFileDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $this->flacManipulator->converter->toMp3(
            'foo.bar',
            $this->tmpDir.'foo.bar'
        );
    }

    public function testExceptionIsThrownWhenMp3InputFileIsInvalid()
    {
        $this->expectException(InvalidAudioFileException::class);

        $this->flacManipulator->converter->toMp3(
            $this->testDir.'samples'.DIRECTORY_SEPARATOR.'test.txt',
            $this->tmpDir.'foo.bar'
        );
    }

    public function testExceptionIsThrownWhenProcessFailsWritingToWav()
    {
        $this->expectException(ProcessFailedException::class);

        $this->flacManipulator->converter->toWav(
            $this->flacManipulator->getFile(),
            $this->tmpDir.'foo.bar'
        );
    }

    public function testExceptionIsThrownWhenWavInputFileDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $this->flacManipulator->converter->toWav(
            'foo.bar',
            $this->tmpDir.'foo.bar'
        );
    }

    public function testExceptionIsThrownWhenWavInputFileIsInvalid()
    {
        $this->expectException(InvalidAudioFileException::class);

        $this->flacManipulator->converter->toWav(
            $this->testDir.'samples'.DIRECTORY_SEPARATOR.'test.txt',
            $this->tmpDir.'foo.bar'
        );
    }

    public function testExceptionIsThrownWhenProcessFailsWritingToAlac()
    {
        $this->expectException(ProcessFailedException::class);

        $this->flacManipulator->converter->toAlac(
            $this->flacManipulator->getFile(),
            $this->tmpDir.'foo.bar'
        );
    }

    public function testExceptionIsThrownWhenAlacInputFileDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $this->flacManipulator->converter->toAlac(
            'foo.bar',
            $this->tmpDir.'foo.bar'
        );
    }

    public function testExceptionIsThrownWhenAlacInputFileIsInvalid()
    {
        $this->expectException(InvalidAudioFileException::class);

        $this->flacManipulator->converter->toAlac(
            $this->testDir.'samples'.DIRECTORY_SEPARATOR.'test.txt',
            $this->tmpDir.'foo.bar'
        );
    }
}
