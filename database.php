<?php declare(strict_types=1);

final class Database {
    /**
     * Establish a connection to the database. Note you'll need to have the database existing
     * of course.
     *
     * Once a connection is established, the tables are generated if they don't exist.
     *
     * @param string $hostname the location of the database
     * @param string $dbname the name of the database, i.e. taskapp or taskapp_test
     * @param string $user the username to access the database with, suggest making user for this app
     * @param string $pass the password to access the database with, suggest making user for this app
     */
    public function __construct(string $hostname, string $dbname, string $user, string $pass) {
        try {
            $dsn = "mysql:host=$hostname;dbname=$dbname;charset=utf8mb4";
            $opt = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            $this->pdo = new PDO($dsn, $user, $pass, $opt);

            $this->_checkDatabaseHasTables();
        } catch(PDOException $e) {
            #json_encode(array("error" => "An error occured when initializing the database: " . $e->getMessage()));
            # silently fail here, check at API entrypoints instead so we don't randomly tell someone
            # this if they don't actually care.

            # may want to write to a logfile here instead.
        }
    }

    /**
     * Create the tasks table if it doesn't exist. Execute pure SQL here, not a problem as if this
     * file is compromised it is probably the least of our worries.
     *
     * @return void
     */
    private function _checkDatabaseHasTables() {
        # this can be pure SQL
        $sql = "CREATE TABLE IF NOT EXISTS tasks (
            id           INTEGER       NOT NULL AUTO_INCREMENT,
            title        VARCHAR(64)   NOT NULL DEFAULT 'No Title',
            content      VARCHAR(255)  NOT NULL,
            date_created TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY(id));";
        $this->pdo->exec($sql);
    }

    /**
     * Response JSON to provide when something goes wrong.
     *
     * @param string $message the message to provide as a key to "error".
     * @return JSON object {"error" => $message}
     */
    private function _errorJson(string $message) {
        return json_encode(array("error" => $message));
    }

    /**
     * Get a single task by its id.
     *
     * @param string $id the unique id of the task.
     * @return JSON object containing the task.
     */
    private function _get(string $id) {
        try {
            $stmnt = $this->pdo->prepare("SELECT * FROM tasks WHERE id=:id");
            $stmnt->bindParam(":id", $id);
            $stmnt->execute();
            $result = $stmnt->fetch(PDO::FETCH_ASSOC);
            return json_encode($result);

        } catch(PDOException $e) {
            return $this->_errorJson("unable to find row with id $id");
        }
    }

    /**
     * Add a task to the database.
     *
     * @param string $title the task's title.
     * @param string $content the content of the task, i.e. its description.
     * @return JSON object containing the added task.
     */
    public function add(string $title, string $content) {
        try {
            $stmnt = $this->pdo->prepare("INSERT INTO tasks (title,content) VALUES (:title, :content)");
            $stmnt->bindParam(":title", $title);
            $stmnt->bindParam(":content", $content);
            $stmnt->execute();
            return $this->_get($this->pdo->lastInsertId());

        } catch(PDOException $e) {
            return $this->_errorJson("unable to add row with keys $title and $content");
        }
    }

    /**
     * Get ALL tasks in the database.
     *
     * @return JSON object which is an array of all tasks.
     */
    public function getAll() {
        if(isset($this->pdo)) {
            try {
                $stmnt = $this->pdo->prepare("SELECT * FROM tasks");
                $stmnt->execute();
                return json_encode($stmnt->fetchAll(PDO::FETCH_ASSOC));
            } catch(PDOException $e) {
                return $this->_errorJson("unable to get all tasks.");
            }
        } else {
            return json_encode(array("error" => "unable to connect to the database."));
        }
    }

    /**
     * Update the task with the given id to have the given title and content.
     *
     * @param string $id the id of the task.
     * @param string $title the new title of the task.
     * @param string $content the new content for the task.
     * @return JSON object containing the updated task.
     */
    public function update(string $id, string $title, string $content) {
        try {
            $stmnt = $this->pdo->prepare("UPDATE tasks SET title=:title, content=:content WHERE id=:id");
            $stmnt->bindParam(":title", $title);
            $stmnt->bindParam(":content", $content);
            $stmnt->bindParam(":id", $id);
            $stmnt->execute();
            return $this->_get($id);
        } catch(PDOException $e) {
            return $this->_errorJson("unable to update $id with values \"$title\" & \"$content\"");
        }
    }

    /**
     * Delete the task with the given id.
     *
     * @param string $id the unique id of the task.
     * @return JSON object {"id" -> id_of_deleted_task}
     */
    public function delete(string $id) {
        try {
            $stmnt = $this->pdo->prepare("DELETE FROM tasks WHERE id=:id");
            $stmnt->bindParam(":id", $id);
            $stmnt->execute();
            return json_encode(array("id" => $id));
        } catch(PDOException $e) {
            return $this->_errorJson("unable to delete $id.");
        }
    }

    /**
     * Check whether or not the database has a task with the given ID.
     *
     * @param string $id the unique id of the task.
     * @return JSON object {"id" => id, "count" => number of matching rows (will be 0 or 1)}
     */
    public function hasTask(string $id) {
        try {
            $stmnt = $this->pdo->prepare("SELECT COUNT(*) FROM tasks WHERE id=:id");
            $stmnt->bindParam(":id", $id);
            $stmnt->execute();
            $result = $stmnt->fetch(PDO::FETCH_ASSOC);
            $count = $result["COUNT(*)"];

            return json_encode(array(
                "id" => $id,
                "count" => $count));

        } catch(PDOException $e) {
            return $this->_errorJson("unable to check if a task exists with id $id.");
        }
    }
}