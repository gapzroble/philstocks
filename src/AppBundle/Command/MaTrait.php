<?php

namespace AppBundle\Command;

/**
 * MA calc
 */
trait MaTrait
{

    protected function calculateMa($symbol, $date)
    {
        $stmt = $this->exec('SELECT date, high, low, close, volume FROM quotes WHERE symbol = ? ORDER BY date ASC', $symbol);
        $quotes = $stmt->fetchAll();
        $count = count($quotes);
        $high = $low = $close = $volume = $pl = array();
        $index = 49;
        for ($i = 0; $i < $count; $i ++) {
            $high[] = $quotes[$i]['high'];
            $low[] = $quotes[$i]['low'];
            $close[] = $quotes[$i]['close'];
            $volume[] = $quotes[$i]['volume'];
            if ($date == $quotes[$i]['date']) {
                $index = $i;
            }
            $pl[$i] = 0;
            if (isset($quotes[$i-1]['close']) && $quotes[$i]['close'] > 0) {
                $diff = $quotes[$i]['close'] - $quotes[$i-1]['close'];
                $pl[$i] = ($diff / $quotes[$i-1]['close']) * 100;
            }
        }
        unset($stmt, $count);
        $low40 = trader_ma($low, 40, TRADER_MA_TYPE_SMA);
        if (!$low40) {
            return;
        }
        $high40 = trader_ma($high, 40, TRADER_MA_TYPE_SMA);
        $ema20 = trader_ma($close, 20, TRADER_MA_TYPE_EMA);
        $ema15 = trader_ma($close, 15, TRADER_MA_TYPE_EMA);
        $ma50 = trader_ma($close, 50, TRADER_MA_TYPE_SMA);
        $vol50 = trader_ma($volume, 50, TRADER_MA_TYPE_SMA);
        unset($high, $low, $volume);
        $i = 0;
        for (; isset($low40[$index]); $index++, $i++) {
            $uptrend = $close[$index] > $ma50[$index];
            $crossLow = $crossHigh = false;
            if (isset($ema20[$index-1]) && isset($low40[$index-1])) {
                $crossLow = $ema20[$index-1] < $low40[$index-1] && $ema20[$index] >= $low40[$index];
                $crossHigh = $ema20[$index-1] < $high40[$index-1] && $ema20[$index] >= $high40[$index];
            }
            $volup = $quotes[$index]['volume'] >= $vol50[$index];
            $vol1m = $vol50[$index] >= 1000000;
            $this->exec(
                'INSERT IGNORE INTO ma (symbol, date, ema20, low40, high40, ma50, ema15, close, uptrend, cross_low, cross_high, vol50, vol_above_ave, vol_1m, pl) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                array($symbol, $quotes[$index]['date'], $ema20[$index], $low40[$index], $high40[$index], $ma50[$index], $ema15[$index], $close[$index], $uptrend, $crossLow, $crossHigh, $vol50[$index], $volup, $vol1m, $pl[$index])
            );
            $this->output->write('.');
        }
        if ($i) {
            $this->output->writeln('');
        }
        unset($high40, $low40, $ema20, $quotes, $index, $i, $ema15, $ma50, $close, $vol50, $pl);
    }
}
