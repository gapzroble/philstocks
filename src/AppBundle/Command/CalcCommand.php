<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputOption;

/**
 * calc
 */
class CalcCommand extends AbstractCommand
{

    use MaTrait;
    use AtrTrait;

    protected function configure()
    {
        $this
            ->setName('quotes:calc')
            ->addOption('all', null, InputOption::VALUE_NONE)
        ;
    }

    protected function doExecute()
    {
        $this->output->writeln('calculate');
        $lastDate = $this->getLastDate();
        $symbols = $this->getSymbols($this->input->getOption('all'));
        $count = count($symbols);
        foreach ($symbols as $i => $symbol) {
            $this->progress(str_pad($symbol, 6, ' ', STR_PAD_LEFT), $i + 1, $count);
            if ($lastDate != ($date = $this->getLastDate($symbol, 'ma'))) {
                $this->calculateMa($symbol, $date);
                gc_collect_cycles();
            }
            $atr = null;
            if ($lastDate != ($date = $this->getLastDate($symbol, 'atr'))) {
                $atr = $this->calculateAtr($symbol, $date);
                gc_collect_cycles();
            }
            $this->calculateAtrPeak($symbol, $atr);
            gc_collect_cycles();
        }
    }
}
