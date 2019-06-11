<?php


use Phinx\Seed\AbstractSeed;

class PointSeeder extends AbstractSeed
{
    private function distance($a, $b)
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
    /**
     * The default view coordinates are 42.15, 24.75. Generate points
     * close to them.
     */
    public function run()
    {
        $data = [];

        /* Generate 100 points */
        for ($order = 0; $order < 10; $order++) {

            $point = [
                'id' => $order,
                'order' => $order,
            ];

            /* Set start point */
            if ($order === 0) {
                $point += [
                    'lat' => 42.15,
                    'lng' => 24.75,
                    'elevation' => 0,
                    'distance' => 0,
                ];
            } else {
                $prev = $data[$order - 1];
                $point += [
                    'lat' => $prev['lat'] + (mt_rand(-10, 10) / 10000),
                    'lng' => $prev['lng'] + (mt_rand(-10, 10) / 10000),
                    'elevation' => $prev['elevation'] + (mt_rand(-10, 10) / 100)
                ];
                $point += [
                    'distance' => $this->distance($prev, $point),
                ];
            }

            $data[] = $point;
        }

        // print_r($data);
        $point = $this->table('point');
        $point->insert($data)->save();
    }
}
