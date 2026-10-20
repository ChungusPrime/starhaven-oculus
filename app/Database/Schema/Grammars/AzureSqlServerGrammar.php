<?php

namespace App\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\SqlServerGrammar;

class AzureSqlServerGrammar extends SqlServerGrammar
{
    /**
     * Compile the SQL needed to drop all tables.
     */
    public function compileDropAllTables(): string
    {
        return "DECLARE @sql NVARCHAR(MAX) = N'';
            SELECT @sql += 'DROP TABLE '
                + QUOTENAME(SCHEMA_NAME(schema_id)) + '.' + QUOTENAME(name) + ';'
            FROM sys.tables;

            EXEC sp_executesql @sql;";
    }

    /**
     * Compile the command to enable foreign key constraints.
     */
    public function compileEnableForeignKeyConstraints(): string
    {
        return "DECLARE @sql NVARCHAR(MAX) = N'';
            SELECT @sql += 'ALTER TABLE '
                + QUOTENAME(SCHEMA_NAME(schema_id)) + '.' + QUOTENAME(name)
                + ' WITH CHECK CHECK CONSTRAINT ALL;'
            FROM sys.tables;

            EXEC sp_executesql @sql;";
    }

    /**
     * Compile the command to disable foreign key constraints.
     */
    public function compileDisableForeignKeyConstraints(): string
    {
        return "DECLARE @sql NVARCHAR(MAX) = N'';
            SELECT @sql += 'ALTER TABLE '
                + QUOTENAME(SCHEMA_NAME(schema_id)) + '.' + QUOTENAME(name)
                + ' NOCHECK CONSTRAINT ALL;'
            FROM sys.tables;

            EXEC sp_executesql @sql;";
    }
}
