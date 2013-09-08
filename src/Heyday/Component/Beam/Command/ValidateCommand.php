<?php

namespace Heyday\Component\Beam\Command;

use Heyday\Component\Beam\Config\BeamConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ValidateCommand
 * @package Heyday\Component\Beam\Command
 */
class ValidateCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this->setName('validate')
            ->addConfigOption();
    }
    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $processor = new Processor();
            $processor->processConfiguration(
                new BeamConfiguration(),
                array(
                    $this->getConfig($input)
                )
            );
            $output->writeln(
                array(
                    $this->formatterHelper->formatSection(
                        'info',
                        'Schema valid',
                        'info'
                    )
                )
            );
            
        } catch (\Exception $e) {
            $this->outputError($output, $e->getMessage());
        }
    }

}