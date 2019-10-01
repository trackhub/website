<?php

use Phinx\Migration\AbstractMigration;

class Places extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE place (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                lat DOUBLE PRECISION NOT NULL,
                lng DOUBLE PRECISION NOT NULL,
                name_en VARCHAR(255) DEFAULT NULL,
                name_bg VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");
    }

    public function down()
    {
        $this->query("DROP TABLE place");
    }
}
