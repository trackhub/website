<?php

use Phinx\Seed\AbstractSeed;
use Symfony\Component\Console\Output\OutputInterface;

class TrackSeeder extends AbstractSeed
{
    const TYPE_CYCLING = 1;
    const TYPE_HIKING = 2;

    const VISIBILITY_PUBLIC = 0;
    const VISIBILITY_UNLISTED = 1;

    /**
     * How many track to generate
     */
    const TRACK_COUNT = 40;

    /**
     * Every N-th track will have 1 more version.
     * If NEW_VERSION_EVERY_NTH_TRACK = 3
     * track0, track1, track2 - 1 version
     * track3, track4, track5 - 2 versions
     * track5 - 3 versions
     */
    const NEW_VERSION_EVERY_NTH_TRACK = self::TRACK_COUNT / 3;

    protected function getVisibility($index): int
    {
        if ($index % 4 === 1) {
            return self::VISIBILITY_UNLISTED;
        }

        return self::VISIBILITY_PUBLIC;
    }

    protected function getType($index): int
    {
        if ($index % 3 == 1) {
            return self::TYPE_HIKING;
        }

        return self::TYPE_CYCLING;
    }

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

        for ($i = 0; $i < self::TRACK_COUNT; $i ++) {
            $trackId = uniqid();
            $data = [
                'id' => $trackId,
                'last_check' => date('Y-m-d H:i:s', strtotime(sprintf("-%d hours", $i))),
                'name' => 'Dummy track ' . $i,
                'point_north_east_lat' => 0,
                'point_north_east_lng' => 0,
                'point_south_west_lat' => 0,
                'point_south_west_lng' => 0,
                'type' => $this->getType($i),
                'created_at' => date('Y-m-d H:i:s', strtotime(sprintf("-%d hours", $i))),
                'visibility' => $this->getVisibility($i),
            ];

            $track->insert($data)->saveData();

            for ($j = 0; $j <= $i; $j += self::NEW_VERSION_EVERY_NTH_TRACK) {
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
                    150 + 5 * (4 * $i - $j),
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
            $lat += 0.00001 * rand(1, 10);
            $lon += 0.00001 * rand(1, 10);
            $elev = $elevStart + $i / 4.0;

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
