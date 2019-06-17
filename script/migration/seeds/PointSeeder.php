<?php

use Phinx\Seed\AbstractSeed;

class PointSeeder extends AbstractSeed
{
    private function distance($a, $b) :float
    {
        $r = 6371000;

        $dLan = deg2rad($b['lat'] - $a['lat']);
        $dLng = deg2rad($b['lng'] - $a['lng']);

        $lat1 = deg2rad($a['lat']);
        $lat2 = deg2rad($b['lat']);

        $a = (sin($dLan / 2) ** 2) + (sin($dLng / 2) ** 2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $c * $r;
    }

    public function getDependencies()
    {
        return [
            'VersionSeeder',
        ];
    }

    /**
     * The default view coordinates are 42.15, 24.75. Generate points
     * close to them.
     */
    public function run()
    {

        if (!$this->hasTable('point')) {
            return;
        }

        $point = $this->table('point');

        /* Disable constrains check during truncate */
        $this->execute('SET FOREIGN_KEY_CHECKS = 0; ');
        $point->truncate();
        $this->execute('SET FOREIGN_KEY_CHECKS = 1; ');

        /* Get versions */
        $versions = $this->fetchAll('SELECT * FROM version');
        $tracks = $this->fetchAll('SELECT * FROM track');

        foreach ($versions as $version) {
            $pointNorthEastLat = -999;
            $pointNorthEastLng = -999;
            $pointSouthWestLat = 999;
            $pointSouthWestLng = 999;

            $data = [];
            $count = 150;
            $a = rand(0, 1000) / 1000;
            $b = rand(0, 1000) / 1000;

            $positiveElevation = 0;
            $negativeElevation = 0;

            echo "Generating " . $count . " points for version: " . $version['id'] . "\n";
            for ($i = 0; $i < $count; $i++) {
                $p = [
                    'id' => uniqid(),
                    'order' => $i,
                    'version_id' => $version['id'],
                ];

                if ($i == 0) {
                    $p += [
                        'lat' => 42.15,
                        'lng' => 24.75,
                        'elevation' => 0,
                        'distance' => 0,
                    ];
                } else {
                    $prev = $data[$i - 1];
                    $p += [
                        'lng' => $prev['lng'] + ($a * 0.0005),
                        'lat' => $prev['lat'] + ($b * 0.0005),
                        'elevation' => $prev['elevation'] + mt_rand(-10, 10),
                    ];
                    $p += [
                        'distance' => $prev['distance'] + $this->distance($prev, $p),
                    ];

                    if ($p['elevation'] > $prev['elevation']) {
                        $positiveElevation += $p['elevation'] - $prev['elevation'];
                    } else {
                        $negativeElevation += $prev['elevation'] - $p['elevation'];
                    }
                }

                $data[] = $p;

                if ($p['lat'] > $pointNorthEastLat) {
                    $pointNorthEastLat = $p['lat'];
                }

                if ($p['lng'] > $pointNorthEastLng) {
                    $pointNorthEastLng = $p['lng'];
                }

                if ($p['lat'] < $pointSouthWestLat) {
                    $pointSouthWestLat = $p['lat'];
                }

                if ($p['lng'] < $pointSouthWestLng) {
                    $pointSouthWestLng = $p['lng'];
                }
            }
            $point->insert($data)->save();

            /* Correct positive and negative elevation */
            $this->execute(
                'UPDATE version
                    SET positive_elevation = '.$positiveElevation.',
                        negative_elevation = '.$negativeElevation.'
                    WHERE version.id = \''.$version['id'].'\';'
            );

            /* Correct some track values */
            $this->execute(
                'UPDATE track 
                    SET point_north_east_lat = '.$pointNorthEastLat.',
                        point_north_east_lng = '.$pointNorthEastLng.',
                        point_south_west_lat = '.$pointSouthWestLat.',
                        point_south_west_lng = '.$pointSouthWestLng.'
                    WHERE track.id = \''. $version['track_id'] .'\';'
            );
        }
    }
}
