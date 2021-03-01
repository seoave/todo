<?php
    session_start();
    /**
     * Connect to DB
     */
    /** @var \PDO $pdo */
    require_once './pdo_ini.php';
    require_once './init_db.php';

    /**
     * User registration &  authorization
     */

    /** new user registration */
    if ($_POST['newlogin'] && $_POST['newpass']) {
        $newLogin = filter_var($_POST['newLogin'], FILTER_SANITIZE_STRING);
        $sth = $pdo->prepare('SELECT username FROM users WHERE username = :username');
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->execute([':username' => $newLogin]);
        $newUserName = $sth->fetch();
        if (!$newUserName) {
            $sth = $pdo->prepare('INSERT INTO users (username, pass) VALUES (:username, :pass) ');
            $sth->execute([':username' => $newLogin, ':pass' => $_POST['newpass']]);
            $_SESSION['userId'] = $pdo->lastInsertId();
            $_SESSION['username'] = $newLogin;
            $errorMessage = 'You are registered successfully';
            $errorMessage .= '<br>Follow <a href="./index.php">link</a> to use your todo list';
            header("location: index.php");
            exit();
        } else {
            $errorMessage = "Sorry, username \"{$_POST['newlogin']}\" is not available";
        }
    } else {
        if ($_POST['newlogin'] && !$_POST['newpass'] || !$_POST['newlogin'] && $_POST['newpass']) {
            $errorMessage = "Please, fill all fields for registration";
        }
    }

    /** user login */
    if ($_POST['login'] && $_POST['pass']) {
        // looking for username
        $sth = $pdo->prepare('SELECT username FROM users WHERE username = :username');
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->execute([':username' => $_POST['login']]);
        $userName = $sth->fetch();

        // looking for pass
        if ($userName) {
            $sth = $pdo->prepare('SELECT pass,id FROM users WHERE username = :username');
            $sth->setFetchMode(\PDO::FETCH_ASSOC);
            $sth->execute([':username' => $_POST['login']]);
            $pass = $sth->fetch();

            if ($_POST['pass'] == $pass['pass']) {
                $_SESSION['userId'] = $pass['id'];
                $_SESSION['username'] = $_POST['login'];
                $errorMessage = 'You login successfully';
                $errorMessage .= '<br>Follow <a href="./index.php">link</a> to use your todo list';
                header("location: index.php");
                // exit();
            } else {
                $errorMessage = "Sorry, your password is wrong";
            }

        } else {
            $errorMessage = "Sorry, username \"{$_POST['login']}\" is not exists";
            $errorMessage .= '<br>Enter other username or <a class="register" href="./registration.php">register</a>';
        }

    } else {
        if ($_POST['login'] && !$_POST['pass'] || !$_POST['login'] && $_POST['pass']) {
            $errorMessage = "Please, fill all fields";
        }
    }

    /**
     * logout
     * clean $_SESSION
     */
    if ($_GET['logout'] == 1) {
        $_SESSION = [];
        header("location: index.php");
        exit();
    }


    /**
     * Lists
     */

    /**
     * get all username lists
     * @param $pdo
     * @return array $lists
     */
    function getAllLists($pdo): array
    {
        require_once './pdo_ini.php';
        $sth = $pdo->prepare('SELECT id, list_name FROM lists WHERE user_id = :user_id ORDER BY created_at DESC');
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->execute([':user_id' => $_SESSION['userId']]);
        return $sth->fetchAll();
    }

    /** set list_id */
    if (!$_SESSION['list_id'] && $_SESSION['userId']) {
        // check if user has list_id
        $sth = $pdo->prepare('SELECT MAX(id) FROM lists WHERE user_id = :user_id');
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->execute([':user_id' => $_SESSION['userId']]);
        $listID = $sth->fetch();

        print_r($listID['MAX(id)']);

        if (!$listID['MAX(id)']) {
            $sth = $pdo->prepare('INSERT INTO lists (created_at,user_id, list_name) VALUES (NOW(),:user_id,:startColumnName) ');
            $sth->execute([':user_id' => $_SESSION['userId'], ':startColumnName' => 'List #1']);
            $_SESSION['list_id'] = $pdo->lastInsertId();
        } else {
            $_SESSION['list_id'] = $listID['MAX(id)'];
        }
    }

    /** add new list */
    if ($_GET['addList'] && $_SESSION['userId']) {
        // request to DB, create new list
        $newList = filter_var($_GET['addList'], FILTER_SANITIZE_STRING);
        $sth = $pdo->prepare('INSERT INTO lists (created_at,user_id,list_name) VALUES (NOW(), :user_id, :list_name)');
        $sth->execute([':user_id' => $_SESSION['userId'], ':list_name' => $newList]);
        header("location: index.php");
        exit();
    }

    /** select list */

    if ($_GET['setList']) {
        $_SESSION['list_id'] = $_GET['setList'];
    }

    /** delete empty list */
    if ($_GET['deletelist']) {
        $sth = $pdo->prepare('SELECT COUNT(id) FROM tasks WHERE list_id = :list_id');
        $sth->execute([':list_id' => $_GET['deletelist']]);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $listHasTasks = $sth->fetch();

        if ($listHasTasks['COUNT(id)'] == 0) {
            $sth = $pdo->prepare('DELETE FROM lists WHERE id = :list_id');
            $sth->execute([':list_id' => $_GET['deletelist']]);
        } else {
            $errorMessage = 'List is not empty. You can delete only list without tasks';
        }
    }

    /**
     * Tasks
     *
     */


    /**
     * get all username tasks
     * @param $pdo
     * @return array
     */
    function getAllTasks($pdo): array
    {
        require_once './pdo_ini.php';
        $sth = $pdo->prepare('SELECT id,title,is_done FROM tasks WHERE list_id = :list_id ORDER BY created_at DESC');
        $sth->execute([':list_id' => $_SESSION['list_id']]);
        $tasks = $sth->fetchAll();

        return $tasks;
    }

    /** add new task */
    if (isset($_GET['addTask']) && !$_GET['addTask'] == '' && $_SESSION['userId']) {
        $newTask = filter_var($_GET['addTask'], FILTER_SANITIZE_STRING);
        $sth = $pdo->prepare('INSERT INTO tasks (list_id, title, is_done, created_at) VALUES (:list_id,:task,0, NOW())');
        $sth->execute([':list_id' => $_SESSION['list_id'], ':task' => $newTask]);
        header("location: index.php");
    }

    /** delete task */
    if (isset($_GET['delete'])) {
        $deleteTask = $_GET['delete'];
        $sth = $pdo->prepare('DELETE FROM tasks WHERE id = :id ');
        $sth->execute([':id' => $deleteTask]);
        header("location: index.php");
    }

    /** done task */
    if (isset($_GET['done'])) {
        $doneTask = $_GET['done'];
        $sth = $pdo->prepare('UPDATE tasks SET is_done = 1 WHERE id = :id ');

        $sth->execute([':id' => $doneTask]);
        header("location: index.php");
    }
