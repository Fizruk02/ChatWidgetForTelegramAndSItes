<?php
namespace app;

class Db
{
    private $pdo;
    private $user = 'u4_teleton';
    private $name = 'u4_teleton';
    private $pass = 'yhQoNtcaG@x2';

    public function connect()
    {
        $this->pdo = new \PDO(
            'mysql:host=localhost;dbname='.$this->name,
            $this->user,
            $this->pass);
    }

    public function select($q, $par = [])
    {
        $sth = $this->pdo->prepare($q);
        $sth->execute($par);
        $resp=$sth->fetchAll(\PDO::FETCH_ASSOC);
        return $resp?:[];
    }
    
    public function single($q, $par = []){
        $resp=$this->select($q, $par);
        return count($resp)?$resp[0]:false;
    }

    public function insert($q, $par = [])
    {
        $sth = $this->pdo->prepare($q);
        $sth->execute($par);
        return $this->pdo->lastInsertId();
    }

    public function update($q, $par = [])
    {
        $sth = $this->pdo->prepare($q);
        $sth->execute($par);
    }
}


