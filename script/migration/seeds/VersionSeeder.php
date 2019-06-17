<?php

use Phinx\Seed\AbstractSeed;

class VersionSeeder extends AbstractSeed
{
    public function getDependencies()
    {
        return [
            'TrackSeeder',
        ];
    }

    public function run()
    {
        if (!$this->hasTable('version')) {
            return;
        }

        $version = $this->table('version');

        /* Disable constrains check during truncate */
        $this->execute('SET FOREIGN_KEY_CHECKS = 0; ');
        $version->truncate();
        $this->execute('SET FOREIGN_KEY_CHECKS = 1; ');

        /* Get all tracks */
        $tracks = $this->fetchAll('SELECT * FROM track');

        foreach ($tracks as $track) {
            $count = rand(1, 5);
            echo "Generating " . $count . " variants for track: " . $track['id'] . "\n";

            for ($i = 0; $i < $count; $i++) {
                $data = [
                    'id' => uniqid(),
                    'track_id' => $track['id'],
                    'name' => "Version " . $i,
                    'positive_elevation' => rand(100, 10000),
                    'negative_elevation' => rand(100, 10000),
                ];

                $version->insert($data)->save();
            }
        }
    }
}
