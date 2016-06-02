<?php

namespace AppBundle\Command;

/**
 * filter
 */
class FilterCommand extends AbstractCommand
{

    protected function configure()
    {
        $this->setName('quotes:filter');
    }

    protected function doExecute()
    {
        $date = $this->getLastDate();

        $stmt = $this->exec('SELECT symbol, uptrend, vol_above_ave, vol_1m, cross_low, cross_high, pl FROM ma WHERE date = ? ORDER BY pl, uptrend, vol_above_ave, vol_1m ASC', $date);
        foreach ($stmt->fetchAll() as $row) {
            $this->writeRow(
                $row['symbol'],
                $row['uptrend'] ? '*' : '',
                $row['vol_above_ave'] ? '*' : '',
                $row['vol_1m'] ? '*' : '',
                $row['cross_low'] ? '*' : '',
                $row['cross_high'] ? '*' : '',
                $row['pl'] != 0 ? number_format($row['pl'], 2).'%' : ''
            );
        }
        $this->writeRow('Symbol', 'Uptrend', 'Vol Up', 'Vol 1m', 'xLow', 'xHigh', 'P/L');
    }

    private function writeRow($args = null)
    {
        $width = 12;
        $args = is_array($args) ? $args : func_get_args();
        foreach ($args as $text) {
            $this->output->write(str_pad($text, $width, ' ', STR_PAD_BOTH));
        }
        $this->output->writeln('');
        $this->output->writeln(str_repeat('-', count($args) * $width));
        unset($width, $args);
    }
}
