<?php

use Phinx\Seed\AbstractSeed;
use Symfony\Component\Console\Output\OutputInterface;

class UserSeeder extends AbstractSeed
{
    /* How many users to generate */
    private const USER_COUNT = 30;

    public function getDependencies()
    {
        return [
            'CleanerSeeder',
        ];
    }

    public function run()
    {
        $data = [];

        for ($i = 0; $i < self::USER_COUNT; $i++) {
            $nickname = uniqid('username ');
            $email = uniqid('email@gmail.com');
            $this->getOutput()->writeln("Generating: {$nickname}", OutputInterface::VERBOSITY_VERY_VERBOSE);
            $data[] = [
                'nickname' => $nickname,
                'email' => $email,
                'enabled' => true,
                'facebook_id' => random_int(1e17, 1e18),
                'roles' => "a:0:{}",
                'terms_accepted' => date('Y-m-d H:i:s')
            ];
        }

        $this->table('user')->insert($data)->save();
    }
}
