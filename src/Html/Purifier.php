<?php

namespace App\Html;

use HTMLPurifier;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Purifier
{
    private HTMLPurifier $htmlPurifier;
    private Logger $logger;

    public function __construct(HTMLPurifier $htmlPurifier, LoggerInterface $logger)
    {
        $this->htmlPurifier = $htmlPurifier;
        $this->logger = $logger;
    }

    public function purify(string $html): string
    {
        $purified = $this->htmlPurifier->purify($html);

        $this->logDifference($html, $purified);

        return $purified;
    }

    /**
     * Log differences during purification.
     * Most of the times this is caused because of difference
     * between html-editor and purifier settings
     */
    private function logDifference(string $original, string $purified): void
    {
        $htmlVerify = preg_replace("/(&nbsp;|\xc2\xa0)/im", ' ', $original);
        $htmlVerify = preg_replace("/(\r\n|\n|\s)/im", '', $htmlVerify);
        $htmlVerify = preg_replace("/(\r\n|\n|\s)/im", '', $htmlVerify);


        $purifiedVerify = preg_replace("/(&nbsp;|\xc2\xa0)/im", ' ', $purified);
        $purifiedVerify = preg_replace("/(\r\n|\n|\s)/im", '', $purifiedVerify);
        $purifiedVerify = preg_replace("/(\r\n|\n|\s)/im", '', $purifiedVerify);


        if ($purifiedVerify !== $htmlVerify) {
            $this->logger->error(
                "Purified text != original html",
                [
                    'original' => $original,
                    'purifiedVerify' => $purifiedVerify,
                    'htmlVerify' => $htmlVerify,
                ]
            );
        }
    }
}
