<?php

namespace AppBundle\Command;

/**
 * Chariot calc
 */
class ChariotCommand extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('quotes:chariot');
    }

    protected function doExecute()
    {
        $lastDate = Helper::getLastDate($this->conn);
        $stmt = $this->exec('SELECT DISTINCT symbol FROM quotes ORDER BY symbol');
        $symbols = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $count = count($symbols);
        foreach ($symbols as $i => $symbol) {
            $this->progress(str_pad($symbol, 6, ' ', STR_PAD_LEFT), $i + 1, $count);
            $stmt = $this->exec('SELECT date FROM chariot WHERE symbol = ? ORDER BY date DESC LIMIT 1', $symbol);
            $last = $stmt->fetch(\PDO::FETCH_COLUMN);
            if ($lastDate != $last) {
                $this->calculate($symbol, $last);
            }
            unset ($last, $stmt);
            gc_collect_cycles();
        }
    }

    private function calculate($symbol, $date)
    {
        $stmt = $this->exec('SELECT date, high, low, close FROM quotes WHERE symbol = ? ORDER BY date ASC', $symbol);
        $quotes = $stmt->fetchAll();
        $count = count($quotes);
        $high = $low = $close = array();
        $index = 39;
        for ($i = 0; $i < $count; $i ++) {
            $high[] = $quotes[$i]['high'];
            $low[] = $quotes[$i]['low'];
            $close[] = $quotes[$i]['close'];
            if ($date == $quotes[$i]['date']) {
                $index = $i;
            }
        }
        unset($stmt, $count);
        $low40 = trader_ma($low, 40, TRADER_MA_TYPE_SMA);
        if (!$low40) {
            return;
        }
        $high40 = trader_ma($high, 40, TRADER_MA_TYPE_SMA);
        $ema20 = trader_ma($close, 20, TRADER_MA_TYPE_EMA);
        unset ($high, $low, $close);
        $i = 0;
        for (; isset($low40[$index]); $index++, $i++) {
            $this->exec(
                'INSERT IGNORE INTO chariot (symbol, date, ema20, low40, high40) VALUES(?, ?, ?, ?, ?)',
                array($symbol, $quotes[$index]['date'], $ema20[$index], $low40[$index], $high40[$index])
            );
            $this->output->write('.');
        }
        if ($i) {
            $this->output->writeln('');
        }
        unset($high40, $low40, $ema20, $quotes, $index, $i);
    }
}
