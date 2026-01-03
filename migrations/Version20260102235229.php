<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102235229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_user (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', full_name VARCHAR(120) NOT NULL, title VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_AD8A54A9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_banner (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', image_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, action_link VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_enabled TINYINT(1) DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_8A9ED5673DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email_verification_code (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(255) DEFAULT NULL, code VARCHAR(10) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used TINYINT(1) NOT NULL, type VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BD2ADC58A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE in_app_notification (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, channel VARCHAR(100) NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', last_sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', expected_consumed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(50) NOT NULL, error_message VARCHAR(255) DEFAULT NULL, attempts INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', attributes JSON DEFAULT NULL, is_enabled TINYINT(1) DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE login_attempts (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(200) NOT NULL, ip_address VARCHAR(45) NOT NULL, user_agent VARCHAR(500) DEFAULT NULL, successful TINYINT(1) NOT NULL, failure_reason VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_login_attempt_email (email), INDEX idx_login_attempt_ip (ip_address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_file (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', filename VARCHAR(255) NOT NULL, mime_type VARCHAR(150) NOT NULL, size INT NOT NULL, storage_path VARCHAR(255) DEFAULT NULL, storage_driver VARCHAR(50) NOT NULL, hash VARCHAR(64) DEFAULT NULL, source VARCHAR(255) NOT NULL, metadata JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_file_version (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', parent_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', variant VARCHAR(50) NOT NULL, storage_path VARCHAR(255) NOT NULL, storage_driver VARCHAR(50) NOT NULL, size INT NOT NULL, metadata JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8D8BC074727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification_deliveries (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', notification_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', channel VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', error_message LONGTEXT DEFAULT NULL, attempts INT DEFAULT NULL, metadata JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9475204AEF1A9D84 (notification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification_templates (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', code VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, body_template LONGTEXT NOT NULL, email_template LONGTEXT DEFAULT NULL, sms_template LONGTEXT DEFAULT NULL, available_variables JSON DEFAULT NULL, notification_type VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_enabled TINYINT(1) DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_C9C13AD177153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notifications (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, data JSON DEFAULT NULL, read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', priority INT DEFAULT NULL, action_url VARCHAR(255) DEFAULT NULL, action_label VARCHAR(100) DEFAULT NULL, expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6000B0D3A76ED395 (user_id), INDEX idx_user_read (user_id, read_at), INDEX idx_user_created (user_id, created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE password_reset_token (token VARCHAR(100) NOT NULL, user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6B7BA4B6A76ED395 (user_id), PRIMARY KEY(token)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permissions (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(100) NOT NULL, name VARCHAR(150) NOT NULL, module VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX permission_code_unique (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_token (token VARCHAR(200) NOT NULL, user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', device_id VARCHAR(200) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C74F2195A76ED395 (user_id), PRIMARY KEY(token)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE static_content (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_STATIC_CONTENT_TYPE (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE system_api_key (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', `key` VARCHAR(100) NOT NULL, name VARCHAR(100) NOT NULL, permissions JSON NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_2561910E8A90ABA9 (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE system_audit_log (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', action VARCHAR(60) NOT NULL, details JSON NOT NULL, actor_id VARCHAR(36) DEFAULT NULL, actor_type VARCHAR(30) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE system_contact_message (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE system_faq (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, question VARCHAR(255) NOT NULL, answer LONGTEXT NOT NULL, position INT NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_2547DF1712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE system_faq_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(100) DEFAULT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE system_setting (`key` VARCHAR(80) NOT NULL, value JSON NOT NULL, description VARCHAR(120) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(`key`)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(200) DEFAULT NULL, email_verified TINYINT(1) NOT NULL, email_verified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_login_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_enabled TINYINT(1) DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE identity_user_role (identity_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_role_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_CF1357EBFF3ED4A8 (identity_id), INDEX IDX_CF1357EB8E0E3CA6 (user_role_id), PRIMARY KEY(identity_id, user_role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_activity (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id VARCHAR(36) NOT NULL, type VARCHAR(50) NOT NULL, payload JSON NOT NULL, actor_id VARCHAR(36) DEFAULT NULL, actor_type VARCHAR(30) DEFAULT NULL, occurred_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_activity_user (user_id), INDEX idx_activity_type (type), INDEX idx_activity_occurred (occurred_at), INDEX idx_activity_actor (actor_type, actor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_credentials (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', relative_user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', password_hash VARCHAR(255) DEFAULT NULL, oauth_providers JSON NOT NULL, two_factor_enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_enabled TINYINT(1) DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_531EE19BE7FBF51B (relative_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_device (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', relative_user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', device_id VARCHAR(200) NOT NULL, push_token VARCHAR(200) DEFAULT NULL, platform VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_enabled TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_6C7DADB3E7FBF51B (relative_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_notification_preferences (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', email_enabled TINYINT(1) DEFAULT 1 NOT NULL, push_enabled TINYINT(1) DEFAULT 1 NOT NULL, sms_enabled TINYINT(1) DEFAULT 0 NOT NULL, enabled_notification_types JSON NOT NULL, channel_preferences JSON NOT NULL, marketing_enabled TINYINT(1) DEFAULT 0 NOT NULL, quiet_hours_start TIME DEFAULT NULL, quiet_hours_end TIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_207F257FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_profile (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', full_name VARCHAR(120) NOT NULL, phone_number VARCHAR(30) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_D95AB405A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_profile_preferences (profile_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', language VARCHAR(10) NOT NULL, marketing_opt_in TINYINT(1) NOT NULL, push_enabled TINYINT(1) NOT NULL, email_enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(profile_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', code VARCHAR(80) NOT NULL, name VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role_permission (user_role_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', permission_id INT NOT NULL, INDEX IDX_7DA194098E0E3CA6 (user_role_id), INDEX IDX_7DA19409FED90CCA (permission_id), PRIMARY KEY(user_role_id, permission_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE admin_user ADD CONSTRAINT FK_AD8A54A9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE app_banner ADD CONSTRAINT FK_8A9ED5673DA5256D FOREIGN KEY (image_id) REFERENCES media_file (id)');
        $this->addSql('ALTER TABLE email_verification_code ADD CONSTRAINT FK_BD2ADC58A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE media_file_version ADD CONSTRAINT FK_8D8BC074727ACA70 FOREIGN KEY (parent_id) REFERENCES media_file (id)');
        $this->addSql('ALTER TABLE notification_deliveries ADD CONSTRAINT FK_9475204AEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notifications (id)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_6B7BA4B6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE system_faq ADD CONSTRAINT FK_2547DF1712469DE2 FOREIGN KEY (category_id) REFERENCES system_faq_category (id)');
        $this->addSql('ALTER TABLE identity_user_role ADD CONSTRAINT FK_CF1357EBFF3ED4A8 FOREIGN KEY (identity_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE identity_user_role ADD CONSTRAINT FK_CF1357EB8E0E3CA6 FOREIGN KEY (user_role_id) REFERENCES user_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_credentials ADD CONSTRAINT FK_531EE19BE7FBF51B FOREIGN KEY (relative_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_device ADD CONSTRAINT FK_6C7DADB3E7FBF51B FOREIGN KEY (relative_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_notification_preferences ADD CONSTRAINT FK_207F257FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB405A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_profile_preferences ADD CONSTRAINT FK_D6EF8FF9CCFA12B8 FOREIGN KEY (profile_id) REFERENCES user_profile (id)');
        $this->addSql('ALTER TABLE user_role_permission ADD CONSTRAINT FK_7DA194098E0E3CA6 FOREIGN KEY (user_role_id) REFERENCES user_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role_permission ADD CONSTRAINT FK_7DA19409FED90CCA FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_user DROP FOREIGN KEY FK_AD8A54A9A76ED395');
        $this->addSql('ALTER TABLE app_banner DROP FOREIGN KEY FK_8A9ED5673DA5256D');
        $this->addSql('ALTER TABLE email_verification_code DROP FOREIGN KEY FK_BD2ADC58A76ED395');
        $this->addSql('ALTER TABLE media_file_version DROP FOREIGN KEY FK_8D8BC074727ACA70');
        $this->addSql('ALTER TABLE notification_deliveries DROP FOREIGN KEY FK_9475204AEF1A9D84');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3A76ED395');
        $this->addSql('ALTER TABLE password_reset_token DROP FOREIGN KEY FK_6B7BA4B6A76ED395');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195A76ED395');
        $this->addSql('ALTER TABLE system_faq DROP FOREIGN KEY FK_2547DF1712469DE2');
        $this->addSql('ALTER TABLE identity_user_role DROP FOREIGN KEY FK_CF1357EBFF3ED4A8');
        $this->addSql('ALTER TABLE identity_user_role DROP FOREIGN KEY FK_CF1357EB8E0E3CA6');
        $this->addSql('ALTER TABLE user_credentials DROP FOREIGN KEY FK_531EE19BE7FBF51B');
        $this->addSql('ALTER TABLE user_device DROP FOREIGN KEY FK_6C7DADB3E7FBF51B');
        $this->addSql('ALTER TABLE user_notification_preferences DROP FOREIGN KEY FK_207F257FA76ED395');
        $this->addSql('ALTER TABLE user_profile DROP FOREIGN KEY FK_D95AB405A76ED395');
        $this->addSql('ALTER TABLE user_profile_preferences DROP FOREIGN KEY FK_D6EF8FF9CCFA12B8');
        $this->addSql('ALTER TABLE user_role_permission DROP FOREIGN KEY FK_7DA194098E0E3CA6');
        $this->addSql('ALTER TABLE user_role_permission DROP FOREIGN KEY FK_7DA19409FED90CCA');
        $this->addSql('DROP TABLE admin_user');
        $this->addSql('DROP TABLE app_banner');
        $this->addSql('DROP TABLE email_verification_code');
        $this->addSql('DROP TABLE in_app_notification');
        $this->addSql('DROP TABLE login_attempts');
        $this->addSql('DROP TABLE media_file');
        $this->addSql('DROP TABLE media_file_version');
        $this->addSql('DROP TABLE notification_deliveries');
        $this->addSql('DROP TABLE notification_templates');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE password_reset_token');
        $this->addSql('DROP TABLE permissions');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE static_content');
        $this->addSql('DROP TABLE system_api_key');
        $this->addSql('DROP TABLE system_audit_log');
        $this->addSql('DROP TABLE system_contact_message');
        $this->addSql('DROP TABLE system_faq');
        $this->addSql('DROP TABLE system_faq_category');
        $this->addSql('DROP TABLE system_setting');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE identity_user_role');
        $this->addSql('DROP TABLE user_activity');
        $this->addSql('DROP TABLE user_credentials');
        $this->addSql('DROP TABLE user_device');
        $this->addSql('DROP TABLE user_notification_preferences');
        $this->addSql('DROP TABLE user_profile');
        $this->addSql('DROP TABLE user_profile_preferences');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE user_role_permission');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
