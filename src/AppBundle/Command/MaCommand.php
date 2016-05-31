<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputOption;

/**
 * MA calc
 */
class MaCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('quotes:ma')
            ->addOption('all', null, InputOption::VALUE_NONE)
        ;
    }

    protected function doExecute()
    {
        $lastDate = Helper::getLastDate($this->conn);
        $symbols = $this->getSymbols($this->input->getOption('all'));
        $count = count($symbols);
        foreach ($symbols as $i => $symbol) {
            $this->progress(str_pad($symbol, 6, ' ', STR_PAD_LEFT), $i + 1, $count);
            $stmt = $this->exec('SELECT date FROM ma WHERE symbol = ? ORDER BY date DESC LIMIT 1', $symbol);
            $last = $stmt->fetch(\PDO::FETCH_COLUMN);
            if ($lastDate != $last) {
                $this->calculate($symbol, $last);
            }
            unset($last, $stmt);
            gc_collect_cycles();
        }
    }

    private function calculate($symbol, $date)
    {
        $stmt = $this->exec('SELECT date, high, low, close FROM quotes WHERE symbol = ? ORDER BY date ASC', $symbol);
        $quotes = $stmt->fetchAll();
        $count = count($quotes);
        $high = $low = $close = array();
        $index = 49;
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
        $ema15 = trader_ma($close, 15, TRADER_MA_TYPE_EMA);
        $ma50 = trader_ma($close, 50, TRADER_MA_TYPE_SMA);
        unset($high, $low);
        $i = 0;
        for (; isset($low40[$index]); $index++, $i++) {
            $uptrend = $ma50[$index] > $close[$index];
            $crossLow = $ema20[$index] > $low40[$index];
            $crossHigh = $ema20[$index] > $high40[$index];
            $this->exec(
                'INSERT IGNORE INTO ma (symbol, date, ema20, low40, high40, ma50, ema15, close, uptrend, cross_low, cross_high) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                array($symbol, $quotes[$index]['date'], $ema20[$index], $low40[$index], $high40[$index], $ma50[$index], $ema15[$index], $close[$index], $uptrend, $crossLow, $crossHigh)
            );
            $this->output->write('.');
        }
        if ($i) {
            $this->output->writeln('');
        }
        unset($high40, $low40, $ema20, $quotes, $index, $i, $ema15, $ma50, $close);
    }
}
