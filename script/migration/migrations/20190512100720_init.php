<?php

use Phinx\Migration\AbstractMigration;

class Init extends AbstractMigration
{
    public function up()
    {
        $this->query("
            CREATE TABLE track (
                id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', 
                name VARCHAR(255) DEFAULT NULL, 
                last_check DATE NOT NULL, 
                point_north_east_lat DOUBLE PRECISION NOT NULL, 
                point_north_east_lng DOUBLE PRECISION NOT NULL, 
                point_south_west_lat DOUBLE PRECISION NOT NULL, 
                point_south_west_lng DOUBLE PRECISION NOT NULL, 
                type INT NOT NULL, 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
            
            CREATE TABLE `user` (
                id INT AUTO_INCREMENT NOT NULL, 
                username VARCHAR(180) NOT NULL, 
                username_canonical VARCHAR(180) NOT NULL, 
                email VARCHAR(180) NOT NULL, 
                email_canonical VARCHAR(180) NOT NULL, 
                enabled TINYINT(1) NOT NULL, 
                salt VARCHAR(255) DEFAULT NULL, 
                password VARCHAR(255) NOT NULL, 
                last_login DATETIME DEFAULT NULL, 
                confirmation_token VARCHAR(180) DEFAULT NULL, 
                password_requested_at DATETIME DEFAULT NULL, 
                roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', 
                facebook_id VARCHAR(255) DEFAULT NULL, 
                UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), 
                UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), 
                UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
            
            CREATE TABLE track_file (id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', version_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', file_content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_11C91E154BBC2705 (version_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
            CREATE TABLE version (id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', file_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', track_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', name VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_BF1CD3C393CB796C (file_id), INDEX IDX_BF1CD3C35ED23C43 (track_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
            CREATE TABLE point (id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', version_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', `order` INT NOT NULL, lat DOUBLE PRECISION NOT NULL, lng DOUBLE PRECISION NOT NULL, elevation DOUBLE PRECISION NOT NULL, distance DOUBLE PRECISION NOT NULL, INDEX IDX_B7A5F3244BBC2705 (version_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
            CREATE TABLE optimized_point (id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', track_id CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', `order` INT NOT NULL, lat DOUBLE PRECISION NOT NULL, lng DOUBLE PRECISION NOT NULL, INDEX IDX_5C2EB3AB5ED23C43 (track_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
            ALTER TABLE track_file ADD CONSTRAINT FK_11C91E154BBC2705 FOREIGN KEY (version_id) REFERENCES version (id);
            ALTER TABLE version ADD CONSTRAINT FK_BF1CD3C393CB796C FOREIGN KEY (file_id) REFERENCES track_file (id);
            ALTER TABLE version ADD CONSTRAINT FK_BF1CD3C35ED23C43 FOREIGN KEY (track_id) REFERENCES track (id);
            ALTER TABLE point ADD CONSTRAINT FK_B7A5F3244BBC2705 FOREIGN KEY (version_id) REFERENCES version (id);
            ALTER TABLE optimized_point ADD CONSTRAINT FK_5C2EB3AB5ED23C43 FOREIGN KEY (track_id) REFERENCES track (id);
        ");
    }

    public function down()
    {
        throw new \Exception("not implemented");
    }
}
