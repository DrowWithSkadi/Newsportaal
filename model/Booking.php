<?php
// Broneeringud (online-registreerimine)
class Booking {

    // Mitu tundi enne algust saab klient broneeringu ise tühistada
    const CANCEL_HOURS = 2;

    /**
     * Tööaja vabad algusajad antud kuupäeval.
     * E–R 9:00–18:00, L 10:00–15:00, P suletud. Samm 30 min.
     */
    public static function timeSlots($date) {
        $ts = strtotime($date);
        if ($ts === false) {
            return array();
        }
        $weekday = (int)date('N', $ts); // 1 = E ... 7 = P
        if ($weekday === 7) {
            return array();
        }
        list($from, $to) = $weekday === 6 ? array(10, 15) : array(9, 18);
        $slots = array();
        for ($m = $from * 60; $m <= $to * 60; $m += 30) {
            $slots[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
        }
        return $slots;
    }

    // Meistri juba broneeritud ajad kuupäeval
    public static function getTakenTimes($masterId, $date) {
        $db = new Database();
        $rows = $db->getAll(
            "SELECT TIME_FORMAT(booking_time, '%H:%i') AS t FROM bookings
             WHERE master_id = ? AND booking_date = ? AND status <> 'cancelled'",
            array((int)$masterId, $date)
        );
        return array_column($rows, 't');
    }

    // Kõik ajad koos infoga, kas aeg on vaba (kasutatakse JS-is)
    public static function getSlotsWithState($masterId, $date) {
        $taken = self::getTakenTimes($masterId, $date);
        $isToday = $date === date('Y-m-d');
        $result = array();
        foreach (self::timeSlots($date) as $slot) {
            $past = $isToday && $slot <= date('H:i');
            $result[] = array('time' => $slot, 'free' => !$past && !in_array($slot, $taken, true));
        }
        return $result;
    }

    /**
     * Uue broneeringu salvestamine.
     * Tagastab array(true, id) või array(false, veateate võti).
     */
    public static function createBooking($data, $userId) {
        $serviceId = isset($data['service_id']) ? (int)$data['service_id'] : 0;
        $masterId  = isset($data['master_id']) ? (int)$data['master_id'] : 0;
        $name      = trim(isset($data['client_name']) ? $data['client_name'] : '');
        $phone     = trim(isset($data['phone']) ? $data['phone'] : '');
        $email     = trim(isset($data['email']) ? $data['email'] : '');
        $date      = isset($data['booking_date']) ? $data['booking_date'] : '';
        $time      = isset($data['booking_time']) ? $data['booking_time'] : '';
        $note      = trim(isset($data['note']) ? $data['note'] : '');

        if (!$serviceId || !$masterId || $name === '' || $phone === '' || $date === '' || $time === '') {
            return array(false, 'err_required');
        }
        if (!preg_match('/^\+?[0-9 ()-]{6,20}$/', $phone)) {
            return array(false, 'err_phone');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return array(false, 'reg_err_email');
        }
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date || $date < date('Y-m-d') || count(self::timeSlots($date)) === 0) {
            return array(false, 'err_date');
        }
        if (!in_array($time, self::timeSlots($date), true) || ($date === date('Y-m-d') && $time <= date('H:i'))) {
            return array(false, 'err_time');
        }

        $service = Service::getServiceByID($serviceId);
        $master  = Master::getMasterByID($masterId);
        if (!$service || !$master || (int)$master['category_id'] !== (int)$service['category_id']) {
            return array(false, 'err_master');
        }
        if (in_array($time, self::getTakenTimes($masterId, $date), true)) {
            return array(false, 'err_taken');
        }

        $db = new Database();
        try {
            $db->executeRun(
                "INSERT INTO bookings (user_id, service_id, master_id, client_name, phone, email, booking_date, booking_time, note)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                array((int)$userId, $serviceId, $masterId, mb_substr($name, 0, 100), $phone, $email !== '' ? $email : null,
                      $date, $time . ':00', $note !== '' ? mb_substr($note, 0, 500) : null)
            );
        } catch (PDOException $e) {
            // unikaalsuse piirang: keegi jõudis sama aja enne broneerida
            return array(false, 'err_taken');
        }
        return array(true, (int)$db->lastInsertId());
    }

    public static function getBookingByID($id) {
        $db = new Database();
        return $db->getOne(
            "SELECT b.*, s.title_et, s.title_ru, s.price, s.duration_min, m.name AS master_name
             FROM bookings b
             JOIN services s ON s.id = b.service_id
             JOIN masters m ON m.id = b.master_id
             WHERE b.id = ?",
            array((int)$id)
        );
    }

    // Kasutaja kõik broneeringud (uuemad eespool)
    public static function getUserBookings($userId) {
        $db = new Database();
        return $db->getAll(
            "SELECT b.*, s.title_et, s.title_ru, s.price, s.duration_min, m.name AS master_name
             FROM bookings b
             JOIN services s ON s.id = b.service_id
             JOIN masters m ON m.id = b.master_id
             WHERE b.user_id = ?
             ORDER BY b.booking_date DESC, b.booking_time DESC",
            array((int)$userId)
        );
    }

    // Kas broneering on veel ees (ja mitte tühistatud)
    public static function isUpcoming($b) {
        return $b['status'] !== 'cancelled' && strtotime($b['booking_date'] . ' ' . $b['booking_time']) > time();
    }

    // Klient saab tühistada kuni CANCEL_HOURS tundi enne algust
    public static function canCancel($b) {
        return $b['status'] !== 'cancelled'
            && strtotime($b['booking_date'] . ' ' . $b['booking_time']) - time() >= self::CANCEL_HOURS * 3600;
    }

    // Tühistamine: ainult oma broneering ja ainult reeglite piires
    public static function cancelByUser($id, $userId) {
        $db = new Database();
        $b = $db->getOne("SELECT * FROM bookings WHERE id = ? AND user_id = ?", array((int)$id, (int)$userId));
        if (!$b || !self::canCancel($b)) {
            return false;
        }
        return $db->executeRun(
            "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?",
            array((int)$id, (int)$userId)
        ) > 0;
    }
}
