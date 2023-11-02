<?php

namespace LinkORB\Component\Sops;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

class Sops
{

    private $sopscmd = 'sops';

    /**
     * Constractor checks if the `sops` command exists on OS.
     */
    public function __construct()
    {
        if (!$this->commandExist($this->sopscmd)) {
            throw new \RuntimeException('Sops command not found. Please install sops via https://github.com/getsops/sops.');
        }
    }


    /**
     * Encrypt a file with a provided key and method.
     * The encryption will be done with `age` method and default `key` saved on OS, in the case $key = NULL
     * In the current version, `age` method was only provided.
     * @param string $filePath The string to a file path to encrypt.
     * @param string $key      The string to a key for encryption. Default `NULL`
     * @param string $method   The string to a method for encryption. Default `age`
     * @return bool True if the encryption is successful.
     */
    public function encrypt($filePath, $key = NULL, $method = 'age')
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('File not found to encrypt. `%s`', $filePath));
        }

        $targetPath = $this->genEncryptPath($filePath);

        if ($key) {
            $cmd = "sops -e --" . $method . " " . $key . " " . $filePath . " > " . $targetPath;
        } else {
            if (getenv('SOPS_AGE_RECIPIENTS') === False) {
                throw new \RuntimeException('You need to set the environment variable `SOPS_AGE_RECIPIENTS` to use age encryption.');
            }
            $cmd = "sops -e " . $filePath . " > " . $targetPath;
        }

        $process = Process::fromShellCommandline($cmd);
        $process->run(null, []);

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return TRUE;
    }

    /**
     * Decrypt a file with a default key and method on OS.
     * @param string $filePath The string to a file path to decrypt.
     * @return bool True if the decryption is successful.
     */
    public function decrypt($filePath)
    {

        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('File not found to decrypt. `%s`', $filePath));
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
