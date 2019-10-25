<?php

class GpxGenerator
{
    public $elevationStart = 100;
    public $pointsCount = 150;

    public function generate(float $latStart = 42, float $lonStart = 24): string
    {
        $data = '<?xml version="1.0"?>
            <gpx version="1.1" creator="track-hub.com: http://track-hub.com/">
            <trk>
            <name>Dummy</name>
            <trkseg>
        ';

        $lat = $latStart;
        $lon = $lonStart;

        for ($i = 0; $i < $this->pointsCount; $i++) {
            $lat += 0.00001 * rand(1, 10);
            $lon += 0.00001 * rand(1, 10);
            $elev = $this->elevationStart + $i / 4.0;

            $data .= '<trkpt lat="' . $lat . '" lon="' . $lon . '" ><ele>' . $elev . '</ele></trkpt>';
        }

        $data .= '
            </trkseg>
            </trk>
            </gpx>
        ';

        return $data;
    }
}
