<?php

namespace AppBundle\Command;

use Symfony\Component\Process\Process;

/**
 * Helper
 */
class Helper
{

    /**
     * @param string $commandLine
     * @return boolean
     */
    public static function runCommand($commandLine)
    {
        $process = new Process($commandLine);
        $process->run();

        return $process->isSuccessful();
    }

    public static function getLastDate($conn)
    {
        $stmt = $conn->prepare('SELECT date FROM quotes ORDER BY date DESC LIMIT 1');
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_COLUMN);
    }
}
