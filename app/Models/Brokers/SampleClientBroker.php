<?php namespace Models\Brokers;

class SampleClientBroker extends Broker
{
    public function findAll()
    {
        return $this->select("SELECT * FROM client");
    }

    public function findById($clientId)
    {
        return $this->selectSingle("SELECT * FROM client WHERE client_id = ?", [$clientId]);
    }
}
