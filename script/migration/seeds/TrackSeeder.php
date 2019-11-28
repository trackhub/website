<?php

use Phinx\Seed\AbstractSeed;
use Symfony\Component\Console\Output\OutputInterface;

class TrackSeeder extends AbstractSeed
{
    private const TYPE_CYCLING = 1;
    private const TYPE_HIKING = 2;

    private const VISIBILITY_PUBLIC = 0;
    private const VISIBILITY_UNLISTED = 1;

    /**
     * Default value for $trackCount
     */
    private const DEFAULT_TRACK_COUNT = 5;

    /**
     * Every N-th track will have 1 more version.
     * If NEW_VERSION_EVERY_NTH_TRACK = 3
     * track0, track1, track2 - 1 version
     * track3, track4, track5 - 2 versions
     * track5 - 3 versions
     */
    private const NEW_VERSION_EVERY_NTH_TRACK = 2;

    /**
     * How many track to generate
     */
    private $trackCount;

    public function getDependencies()
    {
        return [
            'UserSeeder',
        ];
    }

    protected function getVisibility(int $index): int
    {
        if ($index % 4 === 1) {
            return self::VISIBILITY_UNLISTED;
        }

        return self::VISIBILITY_PUBLIC;
    }

    protected function getType(int $index): int
    {
        if ($index % 3 == 1) {
            return self::TYPE_HIKING;
        }

        return self::TYPE_CYCLING;
    }

    protected function getDescriptionEn(int $index): ?string
    {
        if ($index % 2 == 0) {
            return str_repeat(uniqid('en_') . PHP_EOL, $index);
        }

        return null;
    }

    protected function getDescriptionBg(int $index): ?string
    {
        if ($index % 3 == 0) {
            return str_repeat(uniqid('bg_') . PHP_EOL, $index);
        }

        return null;
    }

    protected function init()
    {
        $this->trackCount = env('TRACK_SEEDER_TRACK_COUNT', self::DEFAULT_TRACK_COUNT);
    }

    public function run()
    {
        /**
         * Fetch random users
         */
        $user = $this->fetchRow("SELECT id FROM user ORDER BY RAND() LIMIT 1");

        $track = $this->table('track');

        for ($i = 0; $i < $this->trackCount; $i++) {
            $trackId = uniqid();
            $data = [
                'id' => $trackId,
                'last_check' => date('Y-m-d H:i:s', strtotime(sprintf("-%d hours", $i))),
                'name_en' => 'Dummy track ' . $i,
                'name_bg' => 'Фалшив трак ' . $i,
                'point_north_east_lat' => 0,
                'point_north_east_lng' => 0,
                'point_south_west_lat' => 0,
                'point_south_west_lng' => 0,
                'type' => $this->getType($i),
                'created_at' => date('Y-m-d H:i:s', strtotime(sprintf("-%d hours", $i))),
                'visibility' => $this->getVisibility($i),
                'send_by_id' => $user['id'],
                'description_bg' => $this->getDescriptionBg($i),
                'description_en' => $this->getDescriptionEn($i),
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
                    'send_by_id' => $user['id'],
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

    protected function generateGpxFile(int $pointsCount, float $latStart, float $lonStart, float $elevStart): string
    {
        $gpxGenerator = new GpxGenerator();
        $gpxGenerator->elevationStart = $elevStart;
        $gpxGenerator->pointsCount = $pointsCount;

        return $gpxGenerator->generate($latStart, $lonStart);
    }
}
