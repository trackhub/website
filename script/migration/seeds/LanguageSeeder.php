<?php


use Phinx\Seed\AbstractSeed;


class LanguageSeeder extends AbstractSeed
{
    public function run()
    {
        $languages = $this->table('languages');
        $languages->insert([
            [
                'code' => 'en',
                'englishName' => 'english',
                'nativeName' => 'english'
            ],
            [
                'code' => 'bg',
                'englishName' => 'bulgaria',
                'nativeName' => 'български'
            ],
        ]);

        $languages->save();
    }
}
