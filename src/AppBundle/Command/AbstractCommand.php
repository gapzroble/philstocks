<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
        //$this->output->writeln('<info>Done.');
    }

    abstract protected function doExecute();

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

    protected function getSymbols($all = false)
    {
        if (is_string($all)) {
            $table = $all;
        } else {
            $table = $all ? 'quotes' : 'risky';
        }
        $sql = sprintf('SELECT DISTINCT symbol FROM %s ORDER BY symbol', $table);
        $stmt = $this->exec($sql);
        $symbols = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $symbols;
    }

    protected function getLastDate($symbol = null, $table = 'quotes')
    {
        if ($symbol) {
            $stmt = $this->exec('SELECT date FROM '.$table.' WHERE symbol = ? ORDER BY date DESC LIMIT 1', $symbol);
        } else {
            $stmt = $this->exec('SELECT date FROM '.$table.' ORDER BY date DESC LIMIT 1');
        }
        $date = $stmt->fetch(\PDO::FETCH_COLUMN);
        unset($stmt);

        return $date;
    }

    protected function runCommand($commandLine)
    {
        $process = new Process($commandLine);
        $process->run();

        return $process->isSuccessful();
    }
}
