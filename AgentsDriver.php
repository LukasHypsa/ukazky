<?php

namespace App\Drivers;

class AgentsDriver extends BaseDriver
{

    public function __construct()
    {
        $collumns = [
            "id" => [
                "type" => "int",
                "coded" => 0,
            ],

            "name" => [
                "type" => "string",
                "coded" => 0,
            ],

            "description" => [
                "type" => "string",
                "coded" => 1,
            ],

            "company" => [
                "type" => "string",
                "coded" => 0,
            ],

            "street" => [
                "type" => "string",
                "coded" => 1,
            ],

            "city" => [
                "type" => "string",
                "coded" => 1,
            ],

            "tel" => [
                "type" => "string",
                "coded" => 0,
            ],

            "fax" => [
                "type" => "string",
                "coded" => 0,
            ],

            "email" => [
                "type" => "string",
                "coded" => 0,
            ],

            "web" => [
                "type" => "string",
                "coded" => 0,
            ],
        ];

        parent::__construct($collumns);
    }

    public function getAllAgents($lang) {
        $decodedValues = [];
        $itemsArray = $this->database->table('agents')->fetchAll();

        foreach ($itemsArray as $itemKey => $item)
            foreach ($item as $itemCollumnKey => $itemCollumnValue)
                $decodedValues[$itemKey][$itemCollumnKey] = $this->processDatabaseValue($itemCollumnValue, $itemCollumnKey, $lang);


        $safeValues = $decodedValues;

        return $safeValues;
    }

    public function getAgentById($lang, $id) {
        $item = [];

        $databaseItem = $this->database->table('agents')->where('id = ?', $id)->fetch();

        foreach ($databaseItem as $itemKey => $itemValue)
            $item[$itemKey] = $this->processDatabaseValue($itemValue, $itemKey, $lang);


        return $item;
    }
}