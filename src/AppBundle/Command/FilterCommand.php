<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputOption;

/**
 * filter
 */
class FilterCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('quotes:filter')
            ->addOption('top', null, InputOption::VALUE_NONE)
            ->addOption('up', null, InputOption::VALUE_NONE)
            ->addOption('date', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function doExecute()
    {
        if (!($date = $this->input->getOption('date'))) {
            $date = $this->getLastDate();
        }

        $stmt = $this->exec('SELECT symbol, uptrend, vol_above_ave, vol_1m, cross_low, cross_high, pl FROM ma WHERE date = ? ORDER BY pl, uptrend, vol_above_ave, vol_1m ASC', $date);
        $result = $stmt->fetchAll();
        if ($top = $this->input->getOption('top')) {
            krsort($result);
            $this->output->writeln($date);
            $this->writeRow('Symbol', 'Uptrend', 'Vol Up', 'Vol 1m', 'xLow', 'xHigh', 'P/L');
        }
        foreach ($result as $row) {
            if ($this->input->getOption('up') && $row['pl'] <= 0) {
                continue;
            }
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
        if (!$top) {
            $this->writeRow('Symbol', 'Uptrend', 'Vol Up', 'Vol 1m', 'xLow', 'xHigh', 'P/L');
            $this->output->writeln($date);
        }
    }

    private function writeRow($args = null)
    {
        $width = 8;
        $args = is_array($args) ? $args : func_get_args();
        foreach ($args as $text) {
            $this->output->write(str_pad($text, $width, ' ', STR_PAD_BOTH));
        }
        $this->output->writeln('');
        $this->output->writeln(str_repeat('-', count($args) * $width));
        unset($width, $args);
    }
}
