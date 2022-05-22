<?php

namespace Fc2blog\Service;

use Exception;
use PDO;

// WIP....
class MigrationService
{
    public $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function migrateToLatest(): MigrationResult
    {
        // get latest tick
        $latest_tick = 99999;
        return $this->migrateToTick($latest_tick);
    }

    // 特定のバージョンまで移動するタスクをどうするか…
    public function migrateToTick(string $tick): MigrationResult
    {
        try {
            // get now tick


            // get apply migrationTask list
            /** @var MigrationTask[] $migration_task_list */
            $migration_task_list = []; // getTaskList($from, $to);

            // create transaction
            $this->pdo->beginTransaction();

            // Disable FK consistent check?

            // consume task list
            foreach ($migration_task_list as $migration_task) {
                /** @var MigrationTask $task */
                $task = require $migration_task;
                if (!$task->migrate($this->pdo)) {
                    throw new Exception("migration failed in {$task->name}");
                }
            }

            // Enable FK consistent check?

            // commit transaction
            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return new MigrationResult(
                false,
                $e->getMessage(),
                $e
            );
        }
        return new MigrationResult(
            true,
            "success",
            null
        );
    }


}

class MigrationResult
{
    public $isSuccess;
    public $message;
    public $exception;

    public function __construct(
        bool       $isSuccess,
        string     $message,
        ?Exception $e
    )
    {
        $this->isSuccess = $isSuccess;
        $this->message = $message;
        $this->exception = $e;
    }
}

class MigrationTask
{
    public $name; // ???
    public $tick; // ??? tick番号をココに入れる？

    public function migrate(PDO $pdo): bool
    {
        try {
            // ALTER, CREATE, DROP...

            // MOVE DL DIR CREATE? change permission?

        } catch (\Exception $e) {
            throw $e;
        }
        return true;
    }
}

