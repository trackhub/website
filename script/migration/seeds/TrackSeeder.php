<?php

use Phinx\Seed\AbstractSeed;

class TrackSeeder extends AbstractSeed
{
    const TYPE_CYCLING = 1;
    const TYPE_HIKING = 2;

    const VISIBILITY_PUBLIC = 0;
    const VISIBILITY_UNLISTED = 1;

    public function run()
    {

        if (!$this->hasTable('track')) {
            return;
        }

        $track = $this->table('track');

        /* Disable constrains check during truncate */
        $this->execute('SET FOREIGN_KEY_CHECKS = 0; ');
        $track->truncate();
        $this->execute('SET FOREIGN_KEY_CHECKS = 1; ');

        $count = rand(1, 5);
        for ($i = 0; $i < $count; $i++) {
            $data = [
                'id' => uniqid(),
                'name' => 'Dummy track '.$i,
                'last_check' => date('Y-m-d H:i:s', strtotime(sprintf("+%d hours", $i))),
                'point_north_east_lat' => 0,
                'point_north_east_lng' => 0,
                'point_south_west_lat' => 0,
                'point_south_west_lng' => 0,
                'type' => $this::TYPE_CYCLING,
                'created_at' => date('Y-m-d H:i:s', strtotime(sprintf("+%d hours", $i))),
                'visibility' => $this::VISIBILITY_PUBLIC,
            ];

            $track->insert($data)->save();
        }
    }
}
