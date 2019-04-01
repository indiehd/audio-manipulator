<?php

namespace IndieHD\AudioManipulator\Flac;

use IndieHD\AudioManipulator\Tagging\TaggerInterface;

class FlacTagger implements TaggerInterface
{
    public function writeTags(array $tagData)
    {
        // TODO: Implement writeTags() method.
    }

    public function removeTags(array $data)
    {
        // TODO: Implement removeTags() method.
    }

    public function writeArtwork(string $imagePath)
    {
        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

        setlocale(LC_CTYPE, 'en_US.UTF-8');

        $cmd = 'metaflac --import-picture-from=' . escapeshellarg($imagePath) . ' ' . escapeshellarg($audioFile);

        // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8 character will appear as a "#" symbol.

        $env = ['LC_ALL' => 'en_US.utf8'];

        $res = \GlobalMethods::openProcess($cmd, null, $env);

        if ($res !== false) {
            //As of this writing, metaflac returns an exit status of
            //zero (which cannot necessarily be relied upon on Windows)
            //and does not produce any output on success. The latter fact is
            //far more reliable than the exit status.

            if ($res['stdOut'] == '' && $res['stdErr'] == '') {
                return array('result' => true, 'error' => null);
            } else {
                return [
                    'result' => false,
                    'error' => 'The call to `metaflac` produced output, which'
                        . ' indicates an error condition: ' . \Utility::varToString($res)
                ];
            }
        } else {
            return array('result' => false, 'error' => 'The process could not be opened: ' . $cmd);
        }
    }

    public function removeArtwork()
    {
        // If setlocale(LC_CTYPE, "en_US.UTF-8") is not called here, any UTF-8 character will equate to an empty string.

        setlocale(LC_CTYPE, 'en_US.UTF-8');

        $cmd = 'metaflac --remove --block-type=PICTURE ' . escapeshellarg($file);

        // If "['LC_ALL' => 'en_US.utf8']" is not passed here, any UTF-8 character will appear as a "#" symbol.

        $env = ['LC_ALL' => 'en_US.utf8'];

        $this->process->setTimeout(600);

        $this->process->run($cmd, null, $env);

        if (!$this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        $this->logger->info($cmd . PHP_EOL . PHP_EOL . $this->process->getOutput());

        //As of this writing, metaflac returns an exit status of
        //zero (which cannot necessarily be relied upon on Windows)
        //and does not produce any output on success. The latter fact is
        //far more reliable than the exit status.

        if ($this->process->getOutput() === '' && $this->process->getErrorOutput() === '') {
            return array('result' => true, 'error' => null);
        } else {
            return [
                'result' => false,
                'error' => 'The call to `metaflac` produced output, which'
                    . ' indicates an error condition: (stdout)' . $this->process->getOutput()
                    . ' (stderr) ' . $this->process->getErrorOutput()
            ];
        }
    }
}
