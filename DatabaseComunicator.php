<?php

namespace App\Drivers;

class DatabaseComunicator
{
    /** @var AgentsDriver @inject */
    public $agentsDriver;

    /** @var CountriesDriver @inject */
    public $countriesDriver;

    /** @var PartnersDriver @inject */
    public $partnersDriver;

    /** @var ServicesDriver @inject */
    public $servicesDriver;




    protected $lang;

    public function __construct()
    {
        $this->lang = 'cs';
    }

    public function setLang($lang) {
        $languages = [
            'cs',
            'en',
        ];

        if(in_array($lang, $languages))
            $this->lang = $lang;
    }

    public function getAgentById($id) {
        return $this->agentsDriver->getAgentById($this->lang, $id);
    }

    public function getAllAgents() {
        return $this->agentsDriver->getAllAgents($this->lang);
    }



    public function getCountryById($id) {
        return $this->countriesDriver->getCountryById($this->lang, $id);
    }

    public function getAllCountries() {
        return $this->countriesDriver->getAllCountries($this->lang);
    }



    public function getPartnerById($id) {
        $databasePartner = $this->partnersDriver->getPartnerById($this->lang, $id);
        if($databasePartner == null)
            return null;


        $partner['id'] =  $databasePartner['id'];

        $agentId = $databasePartner['agent'];
        $partner['agent'] = $this->getAgentById($agentId);

        $countryId = $databasePartner['country'];
        $partner['country'] = $this->getCountryById($countryId);


        return $partner;
    }

    public function getAllPartners() {
        $partners = [];
        $databasePartners = $this->partnersDriver->getAllPartners($this->lang);

        foreach ($databasePartners as $databasePartner) {
            $partner['id'] =  $databasePartner['id'];

            $agentId = $databasePartner['agent'];
            $partner['agent'] = $this->getAgentById($agentId);

            $countryId = $databasePartner['country'];
            $partner['country'] = $this->getCountryById($countryId);

            $partners[] = $partner;
        }

        return $partners;
    }



    public function getServiceById($id) {
        return $this->servicesDriver->getServiceById($this->lang, $id);
    }

    public function getAllServices() {
        return $this->servicesDriver->getAllServices($this->lang);
    }
}