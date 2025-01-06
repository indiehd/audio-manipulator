<?php

namespace IndieHD\AudioManipulator\Tests\Validation;

use IndieHD\AudioManipulator\Validation\InvalidAudioFileException;
use IndieHD\AudioManipulator\Validation\ValidatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ValidatorTest extends TestCase
{
    private string $testDir;

    private string $tmpDir;

    public ValidatorInterface $validator;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->testDir = __DIR__.DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR;

        $this->tmpDir = $this->testDir.'storage'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR;

        $this->validator = \IndieHD\AudioManipulator\app()->builder->get('validator');
    }

    public function testExceptionIsThrownWhenInputFileDoesNotExist()
    {
        $this->expectException(FileNotFoundException::class);

        $this->validator->validateAudioFile($this->tmpDir.'non-existent.mp3', 'mp3');
    }

    public function testExceptionIsThrownWhenInputFileTypeIsInvalid()
    {
        $this->expectException(InvalidAudioFileException::class);

        $this->validator->validateAudioFile(
            $this->testDir.'samples'.DIRECTORY_SEPARATOR.'test.flac',
            'fake-type'
        );
    }
}
