<?php
/**
 * Author: Hieu Nguyen
 */

namespace Juno\Minify\Console\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class MinifyStuff extends Command
{
    protected function configure()
    {
        $this->setName('juno:minify_stuff')->setDescription('Minify css/js and optimize image.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $script = $objectManager->create('\Juno\Minify\Cron\Minify');
        $script->execute();
    }
}