<?php

namespace Curl;

/**
 * Curl_Multi
 */
class Multi
{

    /**
     * @param array     $urls
     * @param callbable $writer
     */
    public static function download(array $urls, $writer)
    {
        $master = curl_multi_init();
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Accept-Encoding: gzip,deflate'],
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_VERBOSE => true,
        );
        $writers = array();
        foreach ($urls as $url) {
            $fp = call_user_func($writer, $url);
            if ($fp) {
                $ch = curl_init($url);
                curl_setopt_array($ch, $options);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_multi_add_handle($master, $ch);
                $writers[$url] = $fp;
            }
        }
        do {
            while (($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM) {
            }
            if ($execrun != CURLM_OK) {
                break;
            }
            while ($done = curl_multi_info_read($master)) {
                $info = curl_getinfo($done['handle']);
                if ($info['http_code'] == 200) {
                    curl_multi_remove_handle($master, $done['handle']);
                }
            }
        } while ($running);

        curl_multi_close($master);

        foreach ($writers as $url => $fp) {
            fclose($fp);
        }
    }
}
