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
        $faker = Faker\Factory::create();
        $data = [];

        for ($i = 0; $i < self::USER_COUNT; $i++) {
            $username = $faker->firstName . ' ' . $faker->lastName;
            $email = $faker->email;
            $this->getOutput()->writeln("Generating: {$username}", OutputInterface::VERBOSITY_VERY_VERBOSE);
            $data[] = [
                'username' => $username,
                'username_canonical' => mb_convert_case($username, MB_CASE_LOWER, "UTF-8"),
                'email' => $email,
                'email_canonical' => mb_convert_case($email, MB_CASE_LOWER, "UTF-8"),
                'enabled' => true,
                'password' => sha1(base64_encode(random_bytes(30))),
                'facebook_id' => random_int(1e17, 1e18),
                'roles' => "a:0:{}",
                'terms_accepted' => date('Y-m-d H:i:s')
            ];
        }

        $this->table('user')->insert($data)->save();
    }
}
