<?php

namespace LinkORB\Component\Sops;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

class Sops
{

    private $sopscmd = 'sops';

    public function __construct()
    {
        if (!$this->commandExist($this->sopscmd)) {
            throw new \RuntimeException('Sops command not found. Please install sops via https://github.com/getsops/sops.');
        }
    }

    public function encrypt($key, $filePath, $method = 'age')
    {

        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('File not found to encrypt. %s', $filePath));
        }

        $targetPath = $this->genEncryptPath($filePath);

        $cmd = "sops -e --age " . $key . " " . $filePath . " > " . $targetPath;

        $process = Process::fromShellCommandline($cmd);
        $process->run(null, []);

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return TRUE;
    }

    public function decrypt($filePath)
    {

        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('File not found to decrypt. %s', $filePath));
        }

        $targetPath = $this->genDecryptPath($filePath);

        $cmd = $this->sopscmd . " -d " . $filePath . " > " . $targetPath;

        $process = Process::fromShellCommandline($cmd);
        $process->run(null, []);

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return TRUE;
    }

    private function genEncryptPath($srcPath)
    {
        $path_parts = pathinfo($srcPath);
        return $path_parts['dirname'] . '/' . $path_parts['filename'] . '.' . $this->sopscmd . '.' . $path_parts['extension'];
    }

    private function genDecryptPath($srcPath)
    {
        $path = str_replace($this->sopscmd . '.', '', $srcPath, $count);
        if (!$count) {
            throw new \RuntimeException('Decrypt file name is wrong.');
        }
        return $path;
    }

    private function commandExist($cmd)
    {
        $executableFinder = new ExecutableFinder();
        $return = $executableFinder->find($cmd);
        return $return;
    }
}
