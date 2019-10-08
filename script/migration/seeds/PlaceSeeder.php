<?php

use Phinx\Seed\AbstractSeed;
use Symfony\Component\Console\Output\OutputInterface;

class PlaceSeeder extends AbstractSeed
{
    private const TYPE_GENERIC = 0;
    private const TYPE_DRINKING_FOUNTAIN = 1;

    private const DEFAULT_PLACE_COUNT = 10000;
    private const MAX_USERS = 20;


    private $placeCount;

    public function getDependencies()
    {
        return [
            'UserSeeder',
        ];
    }


    /**
     * Check environment for default value
     */
    protected function init()
    {
        $this->placeCount = env('PLACE_SEEDER_PLACE_COUNT', self::DEFAULT_PLACE_COUNT);
    }

    public function run()
    {
        /* Fetch users */
        $users = $this->fetchAll("SELECT id FROM user ORDER BY RAND() LIMIT " . self::MAX_USERS);
        $place = $this->table('place');

        $this->getOutput()->writeln("Generating " . $this->placeCount . " places", OutputInterface::VERBOSITY_VERY_VERBOSE);
        for ($i = 0; $i < $this->placeCount; $i++) {
            /* Get random user */
            $user = $users[mt_rand(0, count($users) - 1)];

            $data = [
                'id' => uniqid(),
                'name_en' => 'Dummy place ' . $i,
                'name_bg' => 'Фалшиво място ' . $i,
                'lat' => mt_rand(41 * 1000000, 43 * 1000000) / 1000000,
                'lng' => mt_rand(24 * 1000000, 25 * 1000000) / 1000000,
                'type' => random_int(0, 1),
                'created_at' => date('Y-m-d H:i:s', strtotime(sprintf("-%d hours", $i))),
                'send_by_id' => $user['id']
            ];

            $place->insert($data)->saveData();
        }
    }
}
