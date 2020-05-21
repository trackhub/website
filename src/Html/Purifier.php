<?php

namespace App\Html;

use HTMLPurifier;

class Purifier
{
    private HTMLPurifier $htmlPurifier;

    public function __construct(HTMLPurifier $htmlPurifier)
    {
        $this->htmlPurifier = $htmlPurifier;
    }

    public function purify(string $html) : string
    {
        return $this->htmlPurifier->purify($html);
    }
}
