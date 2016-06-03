<?php

namespace AppBundle\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Importer
 */
class ImportCommand extends AbstractCommand
{

    use RiskyTrait;
    use IgnoreTrait;
    use DropboxTrait;
    use SmpTrait;

    protected function configure()
    {
        $this
            ->setName('quotes:import')
            ->addArgument('year', InputArgument::OPTIONAL)
        ;
    }

    protected function doExecute()
    {
        $this->year = $this->input->getArgument('year');
        if ($this->year && $this->year < 2006) {
            throw new \Exception('Invalid year');
        }
        try {
            $this->dlQuotesFromDropbox();
            $this->dlQuotesFromSmp();
            $this->importQuotes();
            $this->updateRisky();
            $this->updateIgnore();
        } catch (\Exception $ex) {
            $this->output->writeln(sprintf('<error>%s: %s', get_class($ex), $ex->getMessage()));
            $this->output->writeln($ex->getTraceAsString());
        }
    }

    private function importQuotes()
    {
        $this->output->writeln('importing quotes');
        $pattern = $this->year ? sprintf('stock*%d*.csv', $this->year) : 'stock*.csv';
        foreach (glob($this->getQuotesDir().'/'.$pattern) as $path) {
            gc_collect_cycles();
            $filename = basename($path);
            $stmt = $this->exec('SELECT filename FROM csv WHERE filename = ?', $filename);
            if ($stmt->rowCount()) {
                unset($stmt);
                continue;
            }
            unset($stmt);
            $this->output->writeln($filename);
            if (($handle = fopen($path, 'r')) !== false) {
                $insert = false;
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (strpos($data[0], '#') === 0 || !isset($data[1])) {
                        continue;
                    }
                    if ($this->ignore($data[0])) {
                        continue;
                    }
                    $dateSeparator = strpos($data[1], '/') !== false ? '/' : '-';
                    list($m, $d, $y) = explode($dateSeparator, $data[1]);
                    $data[1] = "$y-$m-$d";
                    $keys = array('symbol', 'date', 'open', 'high', 'low', 'close', 'volume', 'csv');
                    $data = array_splice($data, 0, count($keys) - 1);
                    $data[] = $filename;
                    $sql = sprintf(
                        'INSERT IGNORE INTO quotes (%s) VALUES(%s)',
                        implode(', ', $keys),
                        implode(',', array_fill_keys(array_keys($data), '?'))
                    );
                    $this->exec($sql, $data);
                    $this->output->write('.');
                    $insert = true;
                    unset($sql, $data, $keys, $dateSeparator);
                }
                fclose($handle);
                gc_collect_cycles();
                if ($insert) {
                    $this->output->writeln('');
                }
            }
            if (!$this->year) {
                $this->exec('INSERT INTO csv (filename) VALUES(?)', $filename);
            }
        }
    }

    private function ignore($symbol)
    {
        static $symbols;
        if (!$symbols) {
            $stmt = $this->exec('SELECT symbol FROM skip');
            $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            $symbols = array_flip($result);
            unset($stmt, $result);
        }

        return isset($symbols[$symbol]);
    }

    private function getQuotesDir()
    {
        static $quotesDir;
        if ($quotesDir === null) {
            $quotesDir = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../var/quotes');
        }

        return $quotesDir;
    }
}
