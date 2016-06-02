<?php

namespace AppBundle\Command;

/**
 * Ignore
 */
trait IgnoreTrait
{

    protected function updateIgnore()
    {
        $this->output->writeln('updating ignore list');
        $symbols = $this->getSymbols(true);
        $now = new \DateTime();
        foreach ($symbols as $symbol) {
            if (strpos($symbol, '^') !== false) {
                $this->output->writeln($symbol);
                $this->ignoreSymbol($symbol);
            } else {
                $date = $this->getLastDate($symbol);
                $dt = new \DateTime($date);
                $mo = $now->diff($dt)->format('%m');
                if ($mo > 1) {
                    $this->output->writeln($symbol);
                    $this->ignoreSymbol($symbol);
                }
            }
        }
    }

    private function ignoreSymbol($symbol)
    {
        $this->exec('INSERT IGNORE INTO skip (symbol) VALUES(?)', $symbol);
        $this->exec('DELETE FROM quotes WHERE symbol = ?', $symbol);
        $this->exec('DELETE FROM risky WHERE symbol = ?', $symbol);
        $this->exec('DELETE FROM ma WHERE symbol = ?', $symbol);
    }
}
