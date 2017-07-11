<?php

declare(strict_types=1);

namespace Alberteddu\Octopus\Command;

use Alberteddu\Octopus\Octopus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Build files and directories using octopus.json.')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cwd = getcwd();
        $file = joinPaths($cwd, 'octopus.json');

        if (!is_readable($file) || !is_file($file)) {
            $output->writeln('<error>Could not find octopus.json</error>');

            return;
        }

        $octopus = new Octopus($input, $output);
        $octopus->buildFromFile($file);
    }
}
