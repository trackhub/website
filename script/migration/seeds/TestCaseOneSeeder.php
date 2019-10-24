<?php

// @FIXME this is WIP

use Phinx\Seed\AbstractSeed;

class TestCaseOneSeeder extends AbstractSeed
{
    public function getDependencies()
    {
        return [
            'CleanerSeeder',
            'UserSeeder',
        ];
    }

    public function run()
    {
        /**
         * Fetch random users
         */
        $user = $this->fetchRow("SELECT id FROM user ORDER BY RAND() LIMIT 1");

        $track = $this->table('track');
        $version = $this->table('version');
        $fileTable = $this->table('track_file');

        $track->insert([
            'id' => 'trackOne',
            'last_check' => date('Y-m-d H:i:s'),
            'name_en' => 'Dummy track 1',
            'name_bg' => 'Фалшив трак 1',
            'point_north_east_lat' => 0,
            'point_north_east_lng' => 0,
            'point_south_west_lat' => 0,
            'point_south_west_lng' => 0,
            'type' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'visibility' => 0,
            'send_by_id' => $user['id']
        ])->saveData();

        $track->insert([
            'id' => 'track Two',
            'last_check' => date('Y-m-d H:i:s'),
            'name_en' => 'Dummy track 2',
            'name_bg' => 'Фалшив трак 2',
            'point_north_east_lat' => 0,
            'point_north_east_lng' => 0,
            'point_south_west_lat' => 0,
            'point_south_west_lng' => 0,
            'type' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'visibility' => 0,
            'send_by_id' => $user['id']
        ])->saveData();

        $versionId = uniqid();

        $this->getOutput()->writeln("Version id: {$versionId}", OutputInterface::VERBOSITY_VERY_VERBOSE);

        $versionData = [
            'id' => $versionId,
            'track_id' => 'trackOne',
            'name' => "Version",
            'positive_elevation' => 0,
            'negative_elevation' => 0,
            'file_id' => null,
            'send_by_id' => $user['id']
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
