<?php

use Phinx\Migration\AbstractMigration;

class SlugByUser extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE track_slug (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                track_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
                slug VARCHAR(255) NOT NULL, 
                INDEX IDX_SLUG_TRACK_ID (track_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");

        $this->query("
            ALTER TABLE track_slug 
            ADD CONSTRAINT FK_SLUG_TRACK FOREIGN KEY (track_id) REFERENCES track (id)
        ");

        $this->query("
            ALTER TABLE track_slug 
            ADD CONSTRAINT FK_SLUG_UNIQUE UNIQUE (slug)
        ");

        $this->query("
            INSERT INTO track_slug(id, track_id, slug)
            SELECT
                UUID(), id, slug
            FROM track
            WHERE slug IS NOT NULL
        ");
    }

    public function down()
    {
        $this->query("DROP TABLE track_slug");
    }
}
