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
        $this->query("DELETE FROM optimized_point");
        $this->query("DELETE FROM track");

        $track = $this->table('track');

        $finder = new \Symfony\Component\Finder\Finder();
        $finder->in(__DIR__ . '/tracks');
        $finder->filter(function(SplFileInfo $file) {
            if ($file->getExtension() === 'gpx') {
                return true;
            }

            return false;
        });
        $finder->depth('==1');

        $processedTracks = 0;
        foreach ($finder as $trackFile) {
            $this->getOutput()->writeln("Processing track " . $trackFile->getFilename());

            $trackId = uniqid();
            $data = [
                'id' => $trackId,
                'name' => 'Dummy track ' . $trackId,
                'last_check' => date('Y-m-d H:i:s',  strtotime(sprintf("-%d hours", $processedTracks))),
                'point_north_east_lat' => 0,
                'point_north_east_lng' => 0,
                'point_south_west_lat' => 0,
                'point_south_west_lng' => 0,
                'type' => $this::TYPE_CYCLING,
                'created_at' => date('Y-m-d H:i:s',  strtotime(sprintf("-%d hours", $processedTracks))),
                'visibility' => $this::VISIBILITY_PUBLIC,
            ];

            $track->insert($data)->save();

            $gpxFileData = file_get_contents($trackFile->getPathname());
            $version = $this->table('version');
            $fileTable = $this->table('track_file');

            $versionId = uniqid('v_');

            $this->getOutput()->writeln("Version id: {$versionId}", OutputInterface::VERBOSITY_VERY_VERBOSE);

            $data = [
                'id' => $versionId,
                'track_id' => $trackId,
                'name' => "Version",
                'positive_elevation' => 0,
                'negative_elevation' => 0,
            ];

            $version->insert($data)->saveData();

            $versionFileData = [
                'id' => uniqid('vf_' . $i . '_'),
                'version_id' => $versionId,
                'created_at' => '2019-01-01 01:01:01',
                'file_content' => $gpxFileData,
            ];

            $this->getOutput()->writeln("File id: {$versionFileData['id']}", OutputInterface::VERBOSITY_VERY_VERBOSE);

            $fileTable->insert($versionFileData)->saveData();

            $this->query("UPDATE version SET file_id = '{$versionFileData['id']}' WHERE id = '{$versionId}'");

            $processedTracks++;
        }

        $track->save();
    }
}
