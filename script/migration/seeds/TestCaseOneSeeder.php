<?php

use Phinx\Seed\AbstractSeed;

class TestCaseOneSeeder extends AbstractSeed
{
    public function getDependencies()
    {
        return [
            'UserSeeder',
        ];
    }

    public function run()
    {
        /** Fetch random users */
        $user = $this->fetchRow("SELECT id FROM user ORDER BY RAND() LIMIT 1");

        $track = $this->table('track');
        $version = $this->table('version');
        $fileTable = $this->table('track_file');
        $placeTable = $this->table('place');

        $gpxGenerator = new GpxGenerator();

        $track->insert([
            'id' => 'track-one',
            'last_check' => date('Y-m-d H:i:s'),
            'name_en' => 'test track 1',
            'name_bg' => 'Тест трак 1',
            'point_north_east_lat' => 0,
            'point_north_east_lng' => 0,
            'point_south_west_lat' => 0,
            'point_south_west_lng' => 0,
            'type' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'visibility' => 0,
            'send_by_id' => $user['id'],
        ])->saveData();

        $versionId = uniqid('t1_v1');
        $versionData = [
            'id' => $versionId,
            'track_id' => 'track-one',
            'name' => "Version",
            'positive_elevation' => 0,
            'negative_elevation' => 0,
            'file_id' => null,
            'send_by_id' => $user['id'],
        ];
        $version->insert($versionData)->saveData();

        $versionFileData = [
            'id' => uniqid('f_t1_v1'),
            'version_id' => $versionId,
            'created_at' => '2019-01-01 01:01:01',
            'file_content' => $gpxGenerator->generate(42, 24),
        ];
        $fileTable->insert($versionFileData)->saveData();

        $this->query("UPDATE version SET file_id = '{$versionFileData['id']}' WHERE id = '{$versionId}'");

        $track->insert([
            'id' => 'track-two',
            'last_check' => date('Y-m-d H:i:s'),
            'name_en' => 'test track 2',
            'name_bg' => 'Тест трак 2',
            'point_north_east_lat' => 0,
            'point_north_east_lng' => 0,
            'point_south_west_lat' => 0,
            'point_south_west_lng' => 0,
            'type' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'visibility' => 0,
            'send_by_id' => $user['id'],
        ])->saveData();

        $versionId = uniqid('t2_v1');

        $versionData = [
            'id' => $versionId,
            'track_id' => 'track-two',
            'name' => "Version",
            'positive_elevation' => 0,
            'negative_elevation' => 0,
            'file_id' => null,
            'send_by_id' => $user['id'],
        ];
        $version->insert($versionData)->saveData();

        $versionFileData = [
            'id' => uniqid('t2_v2'),
            'version_id' => $versionId,
            'created_at' => '2019-01-01 01:01:01',
            'file_content' => $gpxGenerator->generate(43, 24),
        ];
        $fileTable->insert($versionFileData)->saveData();

        $this->query("UPDATE version SET file_id = '{$versionFileData['id']}' WHERE id = '{$versionId}'");

        // track with points in coordinates
        $track->insert([
            'id' => 'track-three',
            'last_check' => date('Y-m-d H:i:s'),
            'name_en' => 'test track 3',
            'name_bg' => 'Тест трак 3',
            'point_north_east_lat' => 0,
            'point_north_east_lng' => 0,
            'point_south_west_lat' => 0,
            'point_south_west_lng' => 0,
            'type' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'visibility' => 0,
            'send_by_id' => $user['id'],
        ])->saveData();

        $versionId = uniqid('t3_v1');
        $versionData = [
            'id' => $versionId,
            'track_id' => 'track-three',
            'name' => "Version",
            'positive_elevation' => 0,
            'negative_elevation' => 0,
            'file_id' => null,
            'send_by_id' => $user['id'],
        ];
        $version->insert($versionData)->saveData();

        $versionFileData = [
            'id' => uniqid('f_t3_v1'),
            'version_id' => $versionId,
            'created_at' => '2019-01-01 01:01:01',
            'file_content' => $gpxGenerator->generate(42, 24),
        ];
        $fileTable->insert($versionFileData)->saveData();

        $placeTable->insert([
            'id' => 'place-for-track-three',
            'lat' => 42.002,
            'lng' => 24.002,
            'name_en' => 'place-for-track-three-name',
            'name_bg' => 'място-за-трак-три-име',
            'send_by_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 1,
        ])->saveData();

        $this->query("UPDATE version SET file_id = '{$versionFileData['id']}' WHERE id = '{$versionId}'");
    }
}
