<?php

namespace Models;

use PDO;
use PDOException;

abstract class DbConnect
{
    private $pdo;

    private function setBdd()
    {
        $host = "localhost";
        $dbname = "guitareforum";
        $port = "3306";
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
        $username = 'root';
        $password = '';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8"
        ];
        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
    protected function getBdd()
    {
        if ($this->pdo === null) {
            $this->setBdd();
        }
        return $this->pdo;
    }
}

        // $host = "localhost";
        // $dbname = "jdr";
        // $port = "3306";


        // $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
        // $username = 'root';
        // $password = '';
        // $options = [
        //     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        //     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        // ];
        // try{
        //     $this->db = new PDO($dsn, $username, $password, $options);
            
        // }catch (PDOException $e){
        //     $error = $e->getMessage();
        // }  