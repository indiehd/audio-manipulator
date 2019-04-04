<?php

namespace IndieHD\AudioManipulator\Tests\Validation;

use PHPUnit\Framework\TestCase;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use IndieHD\AudioManipulator\Validation\InvalidAudioFileException;

class ValidatorTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->testDir = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR;

        $this->validator = \IndieHD\AudioManipulator\app()->builder->get('validator');
    }

    public function testExceptionIsThrownWhenInputFileDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $this->validator->validateAudioFile($this->testDir . 'non-existent.mp3', 'mp3');
    }

    public function testExceptionIsThrownWhenInputFileTypeIsInvalid()
    {
        $this->expectException(InvalidAudioFileException::class);

        $this->validator->validateAudioFile($this->testDir . 'foo.flac', 'fake-type');
    }
}
