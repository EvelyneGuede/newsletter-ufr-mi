-- Migration pour la gestion des demandes de comptes.
-- A executer dans phpMyAdmin sur la base de production.

SET @database_name = DATABASE();

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @database_name
          AND TABLE_NAME = 'utilisateurs'
          AND COLUMN_NAME = 'validation_statut'
    ),
    'SELECT 1',
    "ALTER TABLE utilisateurs ADD COLUMN validation_statut VARCHAR(20) NOT NULL DEFAULT 'accepte'"
);
PREPARE migration_statement FROM @sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @database_name
          AND TABLE_NAME = 'utilisateurs'
          AND COLUMN_NAME = 'date_validation_compte'
    ),
    'SELECT 1',
    'ALTER TABLE utilisateurs ADD COLUMN date_validation_compte DATETIME NULL'
);
PREPARE migration_statement FROM @sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @database_name
          AND TABLE_NAME = 'utilisateurs'
          AND COLUMN_NAME = 'motif_refus'
    ),
    'SELECT 1',
    'ALTER TABLE utilisateurs ADD COLUMN motif_refus TEXT NULL'
);
PREPARE migration_statement FROM @sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
