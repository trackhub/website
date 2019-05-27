<?php

use Phinx\Migration\AbstractMigration;

class TrackSlug extends AbstractMigration
{
    public function up()
    {
        $this->query("
            ALTER TABLE track ADD slug VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down()
    {
        $this->query("
            ALTER TABLE track DROP slug
        ");
    }
}
