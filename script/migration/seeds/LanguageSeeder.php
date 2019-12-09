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
                'englishName' => 'english',
                'nativeName' => 'english'
            ],
            [
                'code' => 'bg',
                'englishName' => 'bulgarian',
                'nativeName' => 'български'
            ],
        ]);

        $languages->save();
    }
}
