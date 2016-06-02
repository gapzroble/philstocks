<?php

namespace AppBundle\Command;

/**
 * ATR calc
 */
trait AtrTrait
{

    protected function calculateAtr($symbol, $date)
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
        $values = array();
        $result = trader_atr($high, $low, $close, 14);
        if ($result) {
            $i = 0;
            for (; isset($result[$index]); $index++, $i++) {
                $this->exec(
                    'INSERT IGNORE INTO atr (symbol, date, value) VALUES(?, ?, ?)',
                    array($symbol, $quotes[$index]['date'], $result[$index])
                );
                $values[] = array(
                    'date' => $quotes[$index]['date'],
                    'value' => $result[$index],
                );
                $this->output->write('.');
            }
            if ($i) {
                $this->output->writeln('');
            }
            unset($i, $result, $index);
        }
        unset($high, $low, $close, $quotes, $result);

        return $values;
    }

    protected function calculateAtrPeak($symbol, $values = array())
    {
        $this->exec('UPDATE atr SET peak = 0, bottom = 0 WHERE symbol = ?', $symbol);
        if (!$values) {
            $stmt = $this->exec('SELECT date, value FROM atr WHERE symbol = ? ORDER BY date ASC', $symbol);
            $values = $stmt->fetchAll();
        }
        $extremes = $this->getExtremes($values);
        if (isset($extremes[1])) {
            $peak = $extremes[0]['value'] > $extremes[1]['value'];
            foreach ($extremes as $row) {
                $bottom = !$peak;
                $this->exec('UPDATE atr SET peak = ?, bottom = ? WHERE symbol = ? AND date = ?', array($peak, $bottom, $symbol, $row['date']));
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
            } elseif ($last['value'] < $curr['value'] && $curr['value'] > $array[$i + 1]['value']) {
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
