<?php

use Phinx\Migration\AbstractMigration;

class Waypoint extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE version_waypoint (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                version_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                lat DOUBLE PRECISION NOT NULL,
                lng DOUBLE PRECISION NOT NULL,
                elevation DOUBLE PRECISION NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                INDEX IDX_D003AAC05ED23C43 (version_id), 
                PRIMARY KEY(id)
            )
            DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");

        $this->query("
            ALTER TABLE version_waypoint 
            ADD CONSTRAINT FK_D003AAC05ED23C43 FOREIGN KEY (version_id) REFERENCES version (id)
        ");
    }

    public function down()
    {
        $this->query("DROP TABLE version_waypoint");
    }
}
