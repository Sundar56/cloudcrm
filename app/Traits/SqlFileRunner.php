<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait SqlFileRunner
{
    protected function runSqlFile(string $path): void
    {
        if (file_exists($path)) {
            DB::unprepared(file_get_contents($path));
        } else {
            throw new \Exception("SQL file not found: $path");
        }
    }
    /**
     * Replace domain in specified columns of a given table.
     *
     * @param string $table Table name.
     * @param array $columns Array of column names where the replacement should happen.
     * @param string $oldDomain Old domain to replace.
     * @param string $newDomain New domain to use.
     */
    public function replaceDomainInTable(string $table, array $columns): void
    {
        $oldDomain = env('OLD_DOMAIN');
        $newDomain = env('NEW_DOMAIN');

        $searchUrl = "https://$oldDomain";
        $replaceUrl = "https://$newDomain";

        $setClauses = [];
        $whereClauses = [];
        $bindings = [];

        foreach ($columns as $column) {
            $setClauses[] = "$column = REPLACE($column, ?, ?)";
            $bindings[] = $searchUrl;
            $bindings[] = $replaceUrl;

            $whereClauses[] = "$column LIKE ?";
            $bindings[] = "%$searchUrl%";
        }

        $setSql = implode(", ", $setClauses);
        $whereSql = implode(" OR ", $whereClauses);

        $sql = "UPDATE {$table} SET {$setSql} WHERE {$whereSql}";

        DB::statement($sql, $bindings);
    }
}
