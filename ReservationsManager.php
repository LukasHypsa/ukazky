<?php

namespace App\Model;

use Nette;
use App\Model\EventsManager;
use App\Model\PackagesManager;
use App\Model\EmailManager;



class ReservationsManager extends BaseManager {
    const OPEN_HOUR = 8;
    const CLOSE_HOUR = 20.5;

    /**
     * @var EventsManager
     * @inject
     */
    public $eventsManager;

    /**
     * @var PackagesManager
     * @inject
     */
    public $packagesManager;

    /**
     * @var EmailManager
     * @inject
     */
    public $emailManager;


    const ID_NAME = 'id';
    const DATABASE_VARIABLES = [
        'name',

    ];

    const DEFAULT_VARIABLE_VALUES = [
        'name' => "hokejová akce"
    ];

    public function __construct(Nette\Database\Context $database) {
        parent::__construct($database);
    }


    public function insert($values) {
        return $this->database->table('reservations')->insert($values);
    }

    public function update($values, $id) {
        return $this->database->table('reservations')->where("id = ?", $id)->update($values);
    }


    /***
     * DELETE
     ***/

    public function deleteReservation($id) {
        $this->deleteDatabaseRecord($id);
    }

    protected function deleteDatabaseRecord($idValue, $idColumnName = 'id') {
        return $this->database->table('reservations')->where($idColumnName, $idValue)->delete();
    }



    public function getById($id) {
        return $this->database->table('reservations')->where('id = ?', $id)->fetch();
    }

    public function getAll() {
        return $this->database->table('reservations')->order("year")->order("month")->order("day")->order("hour")->order("halfhour")->fetchAll();
    }


    ///
    /// vrátí všechny reservace, které jsou ve stejný den a překrývají se disciplínou (tedy překáží)
    ///
    public function getReservationsSameDayAndDiscipline($year, $month, $day, $packageId) {
        // reservace ve stejný den
         $allReservationsThatDay = $this->database->table('reservations')->where('year = ?', $year)->where('month = ?', $month)->where('day = ?', $day)->fetchAll();

         // balíčky překrývající se ve společné disciplíně
         $packagesWithCommonDisciplines = $this->packagesManager->getPackegesWithCommonDisciplines($packageId);

         // chceme jen takové reservace, které jsou na jeden z balíčků se společnou disciplínou
        $wantedReservations = [];

        foreach ($allReservationsThatDay as $reservationsThatDay)
            foreach ($packagesWithCommonDisciplines as $package)
                if($reservationsThatDay->package_id == $package->id)
                {
                    $wantedReservations[] = $reservationsThatDay;
                }

        return $wantedReservations;
    }



    ///
    ///
    ///
    public function getLegitTimesForDay($year, $month, $day, $packageId, $amount) {

        // rezervace v danný den s překrávajícísemi disciplínami
        $reservations = $this->getReservationsSameDayAndDiscipline($year, $month, $day, $packageId);


        // pole se zablokovanými hodinami
        $blockedHours = [];
        if($reservations != null) {

        // disciplíny balíčku
        $disciplines = $this->packagesManager->getDisciplinesOfPackage($packageId);

        $disciplinesChips = [];
        $disciplinesCapacity = [];
        $disciplineIndexes = [];
        //pro každou disciplínu si vytvoříme pole s žetony
        foreach ($disciplines as $discipline)
        {
            $disciplinesChips[$discipline->id] = [];
            $disciplinesCapacity[$discipline->id] = $discipline->capacity;
            $disciplineIndexes[] = $discipline->id;
        }





        // naplníme žetony
        foreach ($reservations as $reservation) {
            // spočítáme výchozí hodinu
            $hourStart = $reservation->hour;
            if($reservation->halfhour != 0)
                $hourStart += 0.5;
            // najdeme si disciplíny rezervace
            $disciplinesOfReservation = $this->packagesManager->getDisciplinesOfPackage($reservation->package_id);
            // najdeme si délku rezervace
            $timeLenght = $this->packagesManager->getById($reservation->package_id)->hours;
            // najdeme si množství lidí
            $amountInReservation = $reservation->amount;

            // a podle času a množství lidí rozdáme žetony
            foreach ($disciplinesOfReservation as $discipline) {
                if(in_array($discipline->id, $disciplineIndexes))
                    for($i = -0.5; $i < $timeLenght; $i+=0.5)
                    {
                        for($j = 0; $j < $amountInReservation; $j++)
                            $disciplinesChips[$discipline->id][] = $hourStart+$i;
                    }
            }

        }



        for($i = self::OPEN_HOUR; $i < (self::CLOSE_HOUR-1); $i+=0.5)
        {
            foreach ($disciplines as $discipline) {
                    for($j = 0; $j < $amount; $j++)
                        $disciplinesChips[$discipline->id][] = $i;
            }
        }


        // nyní jsou v poli všechny záporné žetony
        // nyní je potřeba podle kapacity rozhodnout, která disciplína blokuje jaký čas
        // tyto časy se pak vyškrtnou
        //return $disciplinesChips;






        foreach ($disciplines as $discipline) {
                // pokud $openHour obsahuje $disciplinesChips[$discipline->id]
                // alespoň $disciplinesCapacity[$discipline->id]krát
                // poté přidej $openHour do $blockedHours

                // znásobili jsme 2krát
                foreach ($disciplinesChips[$discipline->id] as $key => $element)
                    $disciplinesChips[$discipline->id][$key] = (int)($element*2);

                // spočítáme množství zíporných žetonů
                $arrayCount = array_count_values($disciplinesChips[$discipline->id]);
                // u každého prvku se podíváme na množství
                foreach ($arrayCount as $index => $hourAmount)
                    // pokud je množství i se započítanými účastníky větší než kapacita
                    if($hourAmount > $disciplinesCapacity[$discipline->id])
                        $blockedHours[] = (float)($index/2.0);
        }

        //return $blockedHours;
        }


        $hoursAsFloats = [];

        for($i = self::OPEN_HOUR; $i < (self::CLOSE_HOUR-1); $i+=0.5)
        {
            $hoursAsFloats[] = $i;
        }


        $finalHours = [];
        foreach ($hoursAsFloats as $openHour)
        {
            if(!in_array($openHour, $blockedHours))
            $finalHours[] = $openHour;
        }




        $hours = [];
        foreach ($finalHours as $hour)
        {
            $word = "";
            $floorHour = floor($hour);
            if($hour<10)
                $word .= "";
            if($floorHour == $hour)
                $word .= $floorHour . ":00";
            else
                $word .= $floorHour . ":30";
            $hours[$hour*2] = $word;
        }


        return $hours;
    }




























    public function sendEmailToAdminNewReservation($reservation) {
        $package = $this->packagesManager->getById($reservation->package_id);
        $packageName = $package->name;

        $time = $reservation->hour . ":";
        $time .= ($reservation->halfhour) ? "30" : "00";

        $date = $reservation->day . "." . $reservation->month . " " . $reservation->year;

        $fullCustomerName = $reservation->name . " " .  $reservation->surname;
        $emailOfCustomer = $reservation->email;
        $noteOfCustomer = $reservation->note;

        if($noteOfCustomer == "")
            $noteOfCustomer = "žádná";


        $sendSuccess = $this->emailManager->sendMail(
            $this->emailManager->returnSystemEmailAddress(),
            $this->emailManager->returnSystemName(),
            $this->emailManager->returnAdminEmailAddress(),
            null,
            "Nová rezervace na webu CrazyHockey",
            'Dobrý den' . "<br>" .
            'na vašem webu se registroval nový zákazník' .
            "<br>" .
            "<br>" .
            "Jméno balíčku: <b>" . $packageName . "</b>" .
            "<br>" .
            "Datum: <b>" . $date . "</b>" .
            "<br>" .
            "Čas: <b>" . $time . "</b>" .
            "<br>" .
            "Jméno účastníka: <b>" . $fullCustomerName . "</b>" .
            "<br>" .
            "Email účastníka: <b>" . $emailOfCustomer . "</b>" .
            "<br>" .
            "Poznámka od účastníka: <b>" . $noteOfCustomer . "</b>" .
            "<br>" .
            "<br>" .
            "Přehled všech zákazníků najdete v administraci." .
            "<br>" .
            "<i>Tento email byl automaticky vygenerován systémem webu CrazyHockey.</i>",
            true

        );

        return $sendSuccess;
    }


    public function sendEmailToCustomerNewReservation($reservation) {
        $package = $this->packagesManager->getById($reservation->package_id);
        $packageName = $package->name;

        $time = $reservation->hour . ":";
        $time .= ($reservation->halfhour) ? "30" : "00";

        $date = $reservation->day . "." . $reservation->month . " " . $reservation->year;

        $fullCustomerName = $reservation->name . " " .  $reservation->surname;
        $emailOfCustomer = $reservation->email;
        $noteOfCustomer = $reservation->note;
        $amount = $reservation->amount;
        $priceName = "price" . $amount;
        $price = $package->$priceName;

        if($noteOfCustomer == "")
            $noteOfCustomer = "žádná";


        $sendSuccess = $this->emailManager->sendMail(
            $this->emailManager->returnSystemEmailAddress(),
            $this->emailManager->returnSystemName(),
            $reservation->email,
            null,
            "Rezervace CrazyHockey",
            'Dobrý den' . "<br>" .
            'děkujeme vám za vaši rezervaci v hokejovém centru CrazyHockey. Níže vám posíláme informace o vaší rezervaci.' .
            "<br>" .
            "<br>" .
            "Typ tréninku, který jste si zarezervoval: <b>" . $packageName . "</b>" .
            "<br>" .
            "Datum: <b>" . $date . "</b>" .
            "<br>" .
            "Čas: <b>" . $time . "</b>" .
            "<br>" .
            "<br>" .
            "Trénink jste si zarezervoval pro <b>" . $amount . " hráče. </b>" . "Cena tréninku je <b>" . $amount . "x " . $price . "Kč.</b>" . "<br>" .
            "<br>" .
            "<br>" .
            "V případě jakýchkoliv dotazů nás neváhejte <a href='http://crazyhockey.lukashypsa.cz/www/kontakt'>kontaktovat</a>" . "<br>" .
            "Děkujeme za vaší rezervaci a doufáme, že si trénink u nás užijete." . "<br>" .
            "<br>" .
            "<i>Tento email byl automaticky vygenerován systémem webu CrazyHockey.</i>",
            true

        );

        return $sendSuccess;
    }

}
