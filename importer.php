<?php
require __DIR__.'/vendor/autoload.php';

use RedBeanPHP\R;

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

debug('downloading zip files');
Curl\Multi::download($dropboxUrls, function ($url, $requireLatest = false) {
    list($year, ) = explode('?', basename($url));
    $filename = __DIR__.'/quotes/'.$year.'.zip';
    if (file_exists($filename)) {
        if ($year == date('Y')) {
            $diff = time() - filemtime($filename);
            if ($diff < $diff * 60 * 5) {
                return null;
            }
        } else {
            return null;
        }
    }

    return fopen($filename, 'w');
});

debug('extracting quotes');
foreach (glob(__DIR__.'/quotes/*.zip') as $zip) {
    $cmd = sprintf('unzip -ov -d %s %s', dirname($zip), $zip);
    exec($cmd);
}

debug('importing quotes');
R::setup('mysql:host=127.0.0.1;dbname=pse', 'root', 'testing');
foreach (glob(__DIR__.'/quotes/*.csv') as $path) {
    $filename = basename($path);
    if (stripos($filename, 'stock') === false) {
        continue;
    }
    $csv = R::find('csv', 'filename = ?', [$filename]);
    if ($csv) {
        continue;
    }
    debug($filename);
    if (($handle = fopen($path, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (strpos($data[0], '#') === 0 || !isset($data[1])) {
                continue;
            }
            list($m, $d, $y) = explode('/', $data[1]);
            $data[1] = "$y-$m-$d";
            $keys = array('symbol', 'date', 'open', 'high', 'low', 'close', 'volume', 'open_interest', 'csv');
            $data = array_splice($data, 0, 8);
            $data[] = $filename;
            $sql = 'INSERT IGNORE INTO quotes (';
            $sql .= implode(', ', $keys);
            $sql .= ') VALUES(\'';
            $sql .= implode("', '", $data);
            $sql .= '\');';
            R::exec($sql);
            echo '.';
        }
        fclose($handle);
        echo PHP_EOL;
    }
    $csv = R::dispense('csv');
    $csv->filename = $filename;
    R::store($csv);
}
R::close();
debug('done.');
