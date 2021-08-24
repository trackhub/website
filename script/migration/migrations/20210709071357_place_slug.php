<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PlaceSlug extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE place ADD slug VARCHAR(255) DEFAULT NULL");

        $this->query("
            CREATE TABLE place_slug (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                place_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', 
                slug VARCHAR(255) NOT NULL, 
                INDEX IDX_SLUG_PLACE_ID (place_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ");

        $this->query("
            ALTER TABLE place_slug 
            ADD CONSTRAINT FK_SLUG_PLACE FOREIGN KEY (place_id) REFERENCES place (id)
        ");

        $this->query("
            ALTER TABLE place_slug 
            ADD CONSTRAINT FK_SLUG_PLACE_UNIQUE UNIQUE (slug)
        ");
    }

    public function down(): void
    {
        $this->execute("DROP TABLE place_slug");
        $this->execute("ALTER TABLE place DROP slug");
    }
}
