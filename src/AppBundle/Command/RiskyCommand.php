<?php

namespace AppBundle\Command;

/**
 * Find risky
 */
class RiskyCommand extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('quotes:risky');
    }

    protected function doExecute()
    {
        $stmt = $this->exec('SELECT symbol, close FROM quotes WHERE date = ?', Helper::getLastDate($this->conn));
        $symbols = $stmt->fetchAll();
        $this->exec('TRUNCATE TABLE risky');
        foreach ($symbols as $row) {
            if ($row['close'] <= 2) {
                $this->output->writeln(str_pad($row['symbol'], 6, ' ', STR_PAD_LEFT).' : '.$row['close']);
                $this->exec('INSERT INTO risky (symbol, current) VALUES (:symbol, :close)', $row);
            }
        }
    }
}
