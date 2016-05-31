<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract Command
 */
abstract class AbstractCommand extends ContainerAwareCommand
{

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->manager = $this->getContainer()->get('doctrine')->getManager();
        $this->conn = $this->manager->getConnection();

        $this->doExecute();
        $this->output->writeln('<info>Done.');
    }

    protected abstract function doExecute();

    protected function exec($sql, $params = null)
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(is_array($params) ? $params : [$params]);

        return $stmt;
    }

    protected function progress($text, $current, $total)
    {
        $this->output->writeln($text.' : '.$current.' of '.$total.' ('.(number_format($current*100/$total, 2)).'%)');
    }
}
