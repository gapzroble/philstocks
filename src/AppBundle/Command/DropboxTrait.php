<?php

namespace AppBundle\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Dropbox dl quotes
 */
trait DropboxTrait
{

    protected function dlQuotesFromDropbox()
    {
        $this->output->writeln('downloading quotes dropbox');
        $client = new Client();
        $promises = array();
        foreach ($this->getUrls() as $url => $destination) {
            $this->output->writeln(basename($destination));
            $promises[$destination] = $client->getAsync($url, array(
                'sink' => $destination,
            ));
        }
        if ($promises) {
            $results = Promise\settle($promises)->wait();
            foreach ($results as $destination => $result) {
                if ($result['state'] == 'fulfilled') {
                    $command = sprintf('unzip -o -d %s %s', dirname($destination), $destination);
                    $this->runCommand($command);
                }
            }
            unset($promises, $results);
        }
        unset($client);
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
}
