<?php


use Phinx\Seed\AbstractSeed;


class LanguageSeeder extends AbstractSeed
{
    public function run()
    {
        $languages = $this->table('language');
        $languages->insert([
            [
                'code' => 'en',
                'name_en' => 'English',
                'name' => 'English'
            ],
            [
                'code' => 'bg',
                'name_en' => 'Bulgarian',
                'name' => 'Български'
            ],
        ]);

        $languages->save();
    }
}
