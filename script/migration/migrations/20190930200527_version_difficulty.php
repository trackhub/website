<?php

use Phinx\Migration\AbstractMigration;

class VersionDifficulty extends AbstractMigration
{
    public function up()
    {
        $this->query("
            ALTER TABLE version
            ADD difficulty 
            ENUM('white', 'green', 'blue', 'black', 'double-black')
            DEFAULT NULL;
        ");
    }

    public function down()
    {
        $this->query("
            ALTER TABLE version
            DROP COLUMN difficulty;
        ");
    }
}
