<?php

use Phinx\Seed\AbstractSeed;

class TrackProcessor extends AbstractSeed
{
    public function getDependencies()
    {
        return [
            'TrackSeeder',
        ];
    }

    public function run()
    {
        $output = $this->getOutput();
        $returnCode = null;
        \exec(
            "cd " . __DIR__ . '/../../../ && ./bin/console app:gps:reprocess -vvv',
            $output,
            $returnCode
        );

        if ($returnCode !== 0) {
            throw new \Exception($output);
        }
    }
}
