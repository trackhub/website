<?php

use Phinx\Migration\AbstractMigration;

class VersionDifficulty extends AbstractMigration
{
    public function up()
    {
        $this->query("
            ALTER TABLE version
            ADD difficulty 
            ENUM('easiest', 'easy', 'more-difficult', 'very-difficult', 'extremely-difficult')
            DEFAULT NULL;"
        );
    }

    public function down()
    {
        $this->query("
            ALTER TABLE version
            DROP COLUMN difficulty;"
        );
    }
}
