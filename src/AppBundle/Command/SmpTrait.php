<?php

namespace AppBundle\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Symfony\Component\Console\Input\InputArgument;

/**
 * SMP forum dl quotes
 */
trait SmpTrait
{

    protected function dlQuotesFromSmp()
    {
        $this->output->writeln('downloading quotes smp');
        $client = new Client();
        $ids = array();
        for ($page = 339;; $page++) {
            $url = $this->getSmpUrl($page);
            $destination = sprintf('%s/%s', $this->getQuotesDir(), basename($url));
            if (file_exists($destination)) {
                $ids = array_merge($ids, $this->extractAttachments(file_get_contents($destination)));
                continue;
            }

            $html = $client->get($url, ['sink' => $destination])->getBody().'';
            $ids = array_merge($ids, $this->extractAttachments($html));

            if (strpos($html, sprintf('pagination_current">%d</span>', $page)) === false) {
                unlink($destination);
                break;
            }
            $findNextPage = sprintf('page-%d.html" class="pagination_next', $page+1);
            if (strpos($html, $findNextPage) === false) {
                unlink($destination);
                break;
            }
        }

        $promises = array();
        foreach (array_unique($ids) as $id) {
            $url = sprintf('http://www.stockmarketpilipinas.com/attachment.php?aid=%d', $id);
            $response = $client->head($url);
            if ($response->hasHeader('Content-disposition')) {
                $value = $response->getHeaderLine('Content-disposition');
                preg_match('#filename="([^"]+)"#', $value, $m);
                if (isset($m[1])) {
                    $destination = sprintf('%s/%s', $this->getQuotesDir(), $m[1]);
                    if (!file_exists($destination)) {
                        $promises[$destination] = $client->getAsync($url, ['sink' => $destination]);
                        $this->output->write('.');
                    }
                }
            }
        }
        if ($promises) {
            Promise\settle($promises)->wait();
            $this->output->writeln('');
        }
    }

    private function extractAttachments($html)
    {
        preg_match_all('#href="attachment\.php\?aid=([0-9]+)"#', $html, $matches);

        return isset($matches[1]) ? $matches[1] : array();
    }

    private function getSmpUrl($page = 1)
    {
        return sprintf('http://www.stockmarketpilipinas.com/thread-337-page-%d.html', $page);
    }
}
