<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputOption;

/**
 * ATR peak calc
 */
class AtrPeakCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('quotes:atr:peak')
        ;
    }

    protected function doExecute()
    {
        $symbols = $this->getSymbols('atr');
        $count = count($symbols);
        foreach ($symbols as $i => $symbol) {
            $this->progress(str_pad($symbol, 6, ' ', STR_PAD_LEFT), $i + 1, $count);
            $this->calculate($symbol);
            gc_collect_cycles();
        }
    }

    private function calculate($symbol)
    {
        $this->exec('UPDATE atr SET peak = 0, bottom = 0 WHERE symbol = ?', $symbol);
        $stmt = $this->exec('SELECT date, value FROM atr WHERE symbol = ? ORDER BY date ASC', $symbol);
        $values = $stmt->fetchAll();
        $extremes = $this->getExtremes($values);
        if (isset($extremes[1])) {
            $peak = $extremes[0]['value'] > $extremes[1]['value'];
            foreach ($extremes as $row) {
                $bottom = !$peak;
                $this->exec('UPDATE atr SET peak = ?, bottom = ? WHERE symbol = ? AND date = ?', array($symbol, $peak, $bottom, $row['date']));
                $peak = !$peak;
            }
            unset($peak);
        }
        unset($stmt, $values, $extremes);
    }

    private function getExtremes($array)
    {
        $extremes = array();
        $last = null;
        $num = count($array);
        for ($i = 0; $i < $num - 1; $i++) {
            $curr = $array[$i];
            if ($last === null) {
                $extremes[] = $curr;
                $last = $curr;
                continue;
            }

            //min
            if ($last['value'] > $curr['value'] && $curr['value'] < $array[$i + 1]['value']) {
                $extremes[] = $curr;
            //maxes
            } else if ($last['value'] < $curr['value'] && $curr['value'] > $array[$i + 1]['value']) {
                $extremes[] = $curr;
            }
            if ($last != $curr['value'] && $curr['value'] != $array[$i + 1]['value']) {
                $last = $curr;
            }
        }
        //add last point
        $extremes[] = $array[$num - 1];

        return $extremes;
    }
}
