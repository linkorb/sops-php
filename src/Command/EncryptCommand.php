<?php

namespace LinkORB\Component\Sops\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LinkORB\Component\Sops\Sops as SopsWrapper;

class EncryptCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('sops:encrypt')
            ->setDescription('Encrypt a YAML, JSON, ENV, INI or BINARY file.')
            ->addArgument('filepath', InputArgument::REQUIRED, 'Filepath to encrypt')
            ->addOption('key', 'k', InputOption::VALUE_OPTIONAL, 'Public key string for encryption')
            ->addOption('method', 'm', InputOption::VALUE_OPTIONAL, 'Method of encryption');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filepath = $input->getArgument('filepath');
        $method = $input->getOption('method') ? $input->getOption('method'): 'age';
        $key = $input->getOption('key') ? $input->getOption('key'): False;

        $output->writeln("<info>Encrypt `$filepath` with `$method`</info>");
        $sops = new SopsWrapper();
        $sops->encrypt($key, $filepath, $method);
        return Command::SUCCESS;
    }
}
