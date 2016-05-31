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
        $lastDate = Helper::getLastDate($this->conn);
        $stmt = $this->exec('SELECT DISTINCT symbol FROM quotes');
        $symbols = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $count = count($symbols);
        unset($stmt);
        foreach ($symbols as $i => $symbol) {
            $this->progress(str_pad($symbol, 6, ' ', STR_PAD_LEFT), $i + 1, $count);
            $stmt = $this->exec('SELECT date FROM atr WHERE symbol = ? ORDER BY date DESC LIMIT 1', $symbol);
            $date = $stmt->fetch(\PDO::FETCH_COLUMN);
            if ($lastDate != $date) {
                $this->calculate($symbol, $date);
            }
            unset($stmt, $date);
            gc_collect_cycles();
        }
    }

    private function calculate($symbol, $date)
    {
        $stmt = $this->exec('SELECT date, high, low, close FROM quotes WHERE symbol = ? ORDER BY date ASC', $symbol);
        $quotes = $stmt->fetchAll();
        $count = count($quotes);
        $high = $low = $close = array();
        $index = 14;
        for ($i = 0; $i < $count; $i ++) {
            $high[] = $quotes[$i]['high'];
            $low[] = $quotes[$i]['low'];
            $close[] = $quotes[$i]['close'];
            if ($date == $quotes[$i]['date']) {
                $index = $i + 1;
            }
        }
        $result = trader_atr($high, $low, $close, 14);
        if ($result) {
            $i = 0;
            for (; isset($result[$index]); $index++, $i++) {
                $this->exec(
                    'INSERT IGNORE INTO atr (symbol, date, value) VALUES(?, ?, ?)',
                    array($symbol, $quotes[$index]['date'], $result[$index])
                );
                $this->output->write('.');
            }
            if ($i) {
                $this->output->writeln('');
            }
            unset($i, $result, $index);
        }
        unset($high, $low, $close, $quotes, $result);
    }
}
