<?php

use Phinx\Migration\AbstractMigration;

class UserRating extends AbstractMigration
{
    public function up()
    {
        /**
         * Create 'rating' table
         */
        $this->query("
            CREATE TABLE rating (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                version_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                user_id INT DEFAULT NULL, rating INT NOT NULL,
                INDEX IDX_D88926224BBC2705 (version_id),
                INDEX IDX_D8892622A76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
        ");

        /**
         * Add constraints
         */
        $this->query("
            ALTER TABLE rating
            ADD CONSTRAINT FK_D88926224BBC2705
            FOREIGN KEY (version_id)
            REFERENCES version (id);
        ");

        $this->query("
            ALTER TABLE rating
            ADD CONSTRAINT FK_D8892622A76ED395
            FOREIGN KEY (user_id)
            REFERENCES `user` (id);
        ");
    }

    public function down()
    {
        /**
         * Drop 'rating' table
         */
        $this->query("
            DROP TABLE
                rating
        ");
    }
}
