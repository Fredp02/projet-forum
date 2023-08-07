<?php
/*

PHPSearch
           __                                    __  
    ____  / /_  ____  ________  ____ ___________/ /_ 
   / __ \/ __ \/ __ \/ ___/ _ \/ __ `/ ___/ ___/ __ \
  / /_/ / / / / /_/ (__  )  __/ /_/ / /  / /__/ / / /
 / .___/_/ /_/ .___/____/\___/\__,_/_/   \___/_/ /_/ 
/_/         /_/                                      

Copyright (c) 2023 by DBSearch <https://github.com/dbsearch>. All rights reserved.

For updates, news, and more, check out:

>> https://github.com/phpsearch/library <<

Note: Delete "namespace..." if you're not using Composer to make everything a bit simpler.
*/
namespace mrfakename\PHPSearch\;

class PHPSearch {
    private $query = null;
    private $connection = null;
    private $stopwords = ['for ', ' and ', 'nor ', 'but ', ' yet', ' or ', ' your ', 'yourself', 'yourselves', ' not ', ' why ', ' do ', ' to ', ' need ', ' my ', ' i ', 'why ', ' me ', ' my ', ' mine ', '.com', '.org', '.net', '.info', '.live', '.online', '.gov', 'best ', ' best '];
    public function __construct($search_string = null, $connection = null) {
        $this->query = $search_string;
        $this->$connection = $connection;
    }
    public function setQuery($search_string) {
        if (empty($search_string)) {
            throw new Exception('Invalid search query passed');
            exit;
        }
        $this->query = $search_string;
    }
    public function setConn($connection) {
        if (!$connection) {
            throw new Exception('Invalid MySQLi connection passed');
            exit;
        }
        $this->connection = $connection;
    }
    private function clean($string) {
        $string = str_replace(' ', '_', $string);
        $string = preg_replace('/[^A-Za-z0-9\_]/', '', $string);
        $string = preg_replace('/_+/', '_', $string);
        return str_replace('_', ' ', $string);
    }
    private function parseQuery($query) {
        $query = strtolower($query);
        $query = trim($query);
        $query = $this->clean($query);
        foreach ($this->stopwords as $stopword) {
            $query = str_replace($stopword, ' ', $query);
        }
        $query = '%' . str_replace(' ', '%', $query) . '%';
        return $query;
    }
    public function search($table, ...$rows) {
        $query = $this->parseQuery($this->query);
        $sql = 'SELECT * FROM `' . mysqli_real_escape_string($this->connection, $table) . '` WHERE';
        $nums = 0;
        $searchArr = [];
        foreach($rows as $row) {
            $sql .= " `" . mysqli_real_escape_string($this->connection, $row) . "` LIKE ? OR";
            array_push($searchArr, $query);
            $nums++;
        }
        $sql = substr_replace($sql, '', -3);
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param(str_repeat('s', $nums), ...$searchArr);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res;
    }
}
