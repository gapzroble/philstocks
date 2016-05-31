<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Ignore
 */
class IgnoreCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('quotes:ignore')
            ->addArgument('symbols', InputArgument::OPTIONAL)
        ;
    }

    protected function doExecute()
    {
        if ($this->input->getArgument('symbols')) {
            $all = array_map('trim', explode(',', $this->input->getArgument('symbols')));
            foreach ($all as $skip) {
                $this->skip($skip);
            }
        }
        $stmt = $this->exec('SELECT DISTINCT symbol FROM quotes ORDER BY symbol');
        $symbols = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $now = new \DateTime();
        foreach ($symbols as $symbol) {
            if (strpos($symbol, '^') !== false) {
                $this->output->writeln($symbol);
                $this->skip($symbol);
            } else {
                $stmt = $this->conn->prepare('SELECT date FROM quotes WHERE symbol = ? ORDER BY date DESC LIMIT 1');
                $stmt->execute([$symbol]);
                $date = $stmt->fetch(\PDO::FETCH_COLUMN);
                $dt = new \DateTime($date);
                $mo = $now->diff($dt)->format('%m');
                if ($mo > 1) {
                    $this->output->writeln($symbol);
                    $this->skip($symbol);
                }
            }
        }
    }

    private function skip($symbol)
    {
        $this->exec('INSERT IGNORE INTO skip (symbol) VALUES(?)', $symbol);
        $this->exec('DELETE FROM quotes WHERE symbol = ?', $symbol);
        $this->exec('DELETE FROM risky WHERE symbol = ?', $symbol);
        $this->exec('DELETE FROM ma WHERE symbol = ?', $symbol);
    }
}
