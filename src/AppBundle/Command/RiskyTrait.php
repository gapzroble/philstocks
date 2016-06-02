<?php

namespace AppBundle\Command;

/**
 * Find risky
 */
trait RiskyTrait
{

    protected function updateRisky()
    {
        $this->output->writeln('updating risky');
        $stmt = $this->exec('SELECT symbol, close FROM quotes WHERE date = ?', $this->getLastDate());
        $symbols = $stmt->fetchAll();
        $this->exec('TRUNCATE TABLE risky');
        foreach ($symbols as $row) {
            if ($row['close'] <= 2) {
                $this->output->writeln(str_pad($row['symbol'], 6, ' ', STR_PAD_LEFT).' : '.$row['close']);
                $this->exec('INSERT INTO risky (symbol) VALUES (?)', $row['symbol']);
            }
        }
    }
}
