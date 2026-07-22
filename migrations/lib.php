<?php
// ============================================================
// migrations/lib.php — Helpers para escribir migraciones idempotentes
// ------------------------------------------------------------
// En MySQL, muchas operaciones DDL (CREATE/ALTER) NO se pueden
// deshacer con transacciones. Por eso cada migración debe ser
// IDEMPOTENTE: comprobar antes de crear/modificar, de forma que
// correr el instalador dos veces nunca falle ni duplique nada.
//
// Usá estos helpers dentro del closure "up" de cada migración.
// ============================================================

/** ¿Existe la tabla en la base actual? */
function mig_table_exists(PDO $db, string $table): bool
{
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = ?"
    );
    $stmt->execute([$table]);
    return (int) $stmt->fetchColumn() > 0;
}

/** ¿Existe la columna en esa tabla? */
function mig_column_exists(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM information_schema.columns
         WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?"
    );
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

/** ¿Existe el índice (por nombre) en esa tabla? */
function mig_index_exists(PDO $db, string $table, string $index): bool
{
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM information_schema.statistics
         WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?"
    );
    $stmt->execute([$table, $index]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Agrega una columna solo si no existe.
 * $definicion es el SQL de la columna, ej: "VARCHAR(120) NOT NULL DEFAULT ''"
 */
function mig_add_column(PDO $db, string $table, string $column, string $definicion): void
{
    if (!mig_column_exists($db, $table, $column)) {
        // Nombres validados contra un patrón seguro (no vienen de input externo,
        // pero por prolijidad evitamos identificadores raros).
        mig_assert_ident($table);
        mig_assert_ident($column);
        $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definicion");
    }
}

/** Crea un índice solo si no existe. $cols ej: "email" o "cliente_id, creado_en" */
function mig_add_index(PDO $db, string $table, string $index, string $cols, bool $unique = false): void
{
    if (!mig_index_exists($db, $table, $index)) {
        mig_assert_ident($table);
        mig_assert_ident($index);
        $tipo = $unique ? 'UNIQUE INDEX' : 'INDEX';
        $db->exec("ALTER TABLE `$table` ADD $tipo `$index` ($cols)");
    }
}

/** Valida que un identificador (tabla/columna/índice) sea seguro. */
function mig_assert_ident(string $ident): void
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $ident)) {
        throw new RuntimeException("Identificador inválido: $ident");
    }
}
