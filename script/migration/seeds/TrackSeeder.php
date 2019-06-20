<?php

use Phinx\Seed\AbstractSeed;
use Symfony\Component\Console\Output\OutputInterface;

class TrackSeeder extends AbstractSeed
{
    const TYPE_CYCLING = 1;
    const TYPE_HIKING = 2;

    const VISIBILITY_PUBLIC = 0;
    const VISIBILITY_UNLISTED = 1;

    public function run()
    {
        $this->query("UPDATE track_file SET version_id = NULL");
        $this->query("UPDATE version SET file_id = NULL");
        $this->query("DELETE FROM point");
        $this->query("DELETE FROM version");
        $this->query("DELETE FROM track_file");
        $this->query("DELETE FROM optimized_point");
        $this->query("DELETE FROM track");

        $track = $this->table('track');

        for ($i = 0; $i < 15; $i ++) {
            $trackId = uniqid();
            $data = [
                'id' => $trackId,
                'last_check' => date('Y-m-d H:i:s', strtotime(sprintf("-%d hours", $i))),
                'name' => 'Dummy track ' . $i,
                'point_north_east_lat' => 0,
                'point_north_east_lng' => 0,
                'point_south_west_lat' => 0,
                'point_south_west_lng' => 0,
                'type' => self::TYPE_CYCLING,
                'created_at' => date('Y-m-d H:i:s', strtotime(sprintf("-%d hours", $i))),
                'visibility' => self::VISIBILITY_PUBLIC,
            ];

            $track->insert($data)->saveData();

            for ($j = 0; $j <= $i; $j += 5) {
                $version = $this->table('version');
                $fileTable = $this->table('track_file');

                $versionId = uniqid();

                $this->getOutput()->writeln("Version id: {$versionId}", OutputInterface::VERBOSITY_VERY_VERBOSE);

                $versionData = [
                    'id' => $versionId,
                    'track_id' => $trackId,
                    'name' => "Version",
                    'positive_elevation' => 0,
                    'negative_elevation' => 0,
                    'file_id' => null,
                ];

                $version->insert($versionData)->saveData();

                $gpxFileData = $this->generateGpxFile(
                    100 + 5 * ($i + $j),
                    42 + $i / 1000.0,
                    24 + $i / 15.0,
                    100
                );

                $versionFileData = [
                    'id' => uniqid(),
                    'version_id' => $versionId,
                    'created_at' => '2019-01-01 01:01:01',
                    'file_content' => $gpxFileData,
                ];

                $this->getOutput()->writeln("File id: {$versionFileData['id']}", OutputInterface::VERBOSITY_VERY_VERBOSE);

                $fileTable->insert($versionFileData)->saveData();

                $this->query("UPDATE version SET file_id = '{$versionFileData['id']}' WHERE id = '{$versionId}'");
            }
        }
    }

    protected function generateGpxFile(int $pintsCount, float $latStart, float $lonStart, float $elevStart): string
    {
        $data = '<?xml version="1.0"?>
            <gpx version="1.1" creator="track-hub.com: http://track-hub.com/">
            <trk>
            <name>Dummy</name>
            <trkseg>
        ';

        $lat = $latStart;
        $lon = $lonStart;

        for ($i = 0; $i < $pintsCount; $i++) {
            $lat = $lat + $i * 0.00001 * rand(1, 3);
            $lon = $lon + $i * 0.00001 * rand(1, 3);
            $elev = $elevStart + $i / 10;

            $data .= '<trkpt lat = "' . $lat . '" lon = "' . $lon . '" ><ele>' . $elev . '</ele></trkpt>';
        }

        $data .= ';
            </trkseg>
            </trk>
            </gpx>
        ';

        return $data;
    }
}
