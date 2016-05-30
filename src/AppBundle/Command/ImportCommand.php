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
            $this->downloadQuotes();
            $this->importQuotes();
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

    private function downloadQuotes($debug = false)
    {
        $this->output->writeln('downloading quotes');
        $client = new Client();
        $promises = array();
        foreach ($this->getUrls() as $url => $destination) {
            $promises[$destination] = $client->getAsync($url, array(
                'sink' => $destination,
                'debug' => $debug,
            ));
        }
        unset($client);
        if ($promises) {
            $results = Promise\settle($promises)->wait();
            foreach ($results as $destination => $result) {
                if ($result['state'] == 'fullfilled') {
                    Helper::runCommand(sprintf('unzip -oq -d %s %s', dirname($destination), $destination));
                }
            }
            unset($promises, $results);
        }
        gc_collect_cycles();
    }

    private function getUrls()
    {
        // https://www.dropbox.com/sh/1dluf0lawy9a7rm/fHREemAjWS
        $dropboxUrls = array(
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AADN2LQYkEUUKi07KwCnY5BTa/2006?dl=1',
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AAAXKzC-HFwtEORPRwquepHra/2007?dl=1',
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AADhK3pHkLHONq6jRVW_Ytrna/2008?dl=1',
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AAB5fGCL_TGQ0StwYsu_GyVsa/2009?dl=1',
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AABeHR1UTm54GodEbjvA0mrXa/2010?dl=1',
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AACmxqZ-MEjmzCNApmW614YTa/2011?dl=1',
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AABqXbgH84lS6NB-vpAsEiPBa/2012?dl=1',
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AABBSyKhfjOZ-tP2OM8_UYU7a/2013?dl=1',
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AADwhfNwFRVoQg5TaqOaVFs9a/2014?dl=1',
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AACh8nCUuvTvP4YdVEH29On2a/2015?dl=1',
            'https://www.dropbox.com/sh/1dluf0lawy9a7rm/AAAaQ8rHfEB_2HsAfjVRUi-8a/2016?dl=1',
        );
        foreach ($dropboxUrls as $url) {
            $year = basename(parse_url($url)['path']);
            $destination = $this->getQuotesDir().'/'.$year.'.zip';
            if (file_exists($destination)) {
                if ($year == date('Y')) {
                    $diff = time() - filemtime($destination);
                    if ($diff < 60 * 60 * 5) {
                        continue;
                    }
                } else {
                    continue;
                }
            }
            yield $url => $destination ;
        }
        unset($dropboxUrls);
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
