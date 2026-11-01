<?php

declare(strict_types=1);

namespace Core;

use PDO;

// Every model gets $this->db (one shared database connection).
// Use it directly ($this->db->prepare(), ->lastInsertId()), and $this->transaction(fn () => ...) for all-or-nothing changes.
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // Run several changes as ONE: if anything inside fails, every change is undone (and the error is passed on).
    // If a transaction is already running (one model calling another), the work simply joins it.
    protected function transaction(callable $work): mixed
    {
        if ($this->db->inTransaction())
        {
            return $work();
        }

        $this->db->beginTransaction();

        try
        {
            $result = $work();
            $this->db->commit();

            return $result;
        }
        catch (\Throwable $error)
        {
            $this->db->rollBack();
            throw $error;
        }
    }

    // [3, 7] → "3,7" for an IN (…) list; whole numbers only, so it is safe inside SQL. An empty list → "0" (matches nothing)
    protected static function idList(array $ids): string
    {
        return $ids === [] ? '0' : implode(',', array_map('intval', $ids));
    }

    // One column of a query as a list of ids (an optional :me value)
    protected function ids(string $sql, ?int $me = null): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($me === null ? [] : ['me' => $me]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    // Two columns of a query as [id => name] (an optional :me value)
    protected function pairs(string $sql, ?int $me = null): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($me === null ? [] : ['me' => $me]);

        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}