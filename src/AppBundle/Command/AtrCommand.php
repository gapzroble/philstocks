<?php

namespace AppBundle\Command;

/**
 * ATR calc
 */
class AtrCommand extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('quotes:atr');
    }

    protected function doExecute()
    {
        $stmt = $this->execute('SELECT DISTINCT symbol FROM quotes');
        $symbols = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($symbols as $symbol) {
            $this->calculate($symbol);
        }
    }

    private function calculate($symbol)
    {
        $symbol = 'PXP';
        $stmt = $this->exec('SELECT * FROM quotes WHERE symbol = ? ORDER BY id ASC /*LIMIT 20*/', $symbol);
        $quotes = $stmt->fetchAll();
        $count = count($quotes);
        $high = $low = $close = array();
        for ($i = 0; $i < $count; $i ++) {
            $high[] = $quotes[$i]['high'];
            $low[] = $quotes[$i]['low'];
            $close[] = $quotes[$i]['close'];
        }
        $result = trader_atr($high, $low, $close, 14);
        print_r($result);
        $this->output->writeln($symbol);
        exit;
    }
}
