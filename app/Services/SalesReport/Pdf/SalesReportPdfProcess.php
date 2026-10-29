<?php

namespace App\Services\SalesReport\Pdf;

use RuntimeException;
use Symfony\Component\Process\Process;

class SalesReportPdfProcess
{
    public function run($binary, array $arguments, $timeout = 60)
    {
        $process = new Process(array_merge(array($binary), $arguments));
        $process->setTimeout(max(1, (int) $timeout));
        $process->run();

        if (!$process->isSuccessful()) {
            $message = trim($process->getErrorOutput());
            if ($message === '') {
                $message = trim($process->getOutput());
            }
            throw new RuntimeException(
                basename($binary) . ' failed with exit code ' . $process->getExitCode() .
                ($message === '' ? '.' : ': ' . $message)
            );
        }

        return array(
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput(),
            'command' => $process->getCommandLine(),
        );
    }
}
