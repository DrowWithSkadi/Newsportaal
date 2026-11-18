<?php
class modelAdminBooking {

    const STATUSES = array('new' => 'Uus', 'confirmed' => 'Kinnitatud', 'cancelled' => 'Tühistatud');

    public static function getBookingList($status = '', $upcomingOnly = false) {
        $where = array();
        $params = array();
        if (isset(self::STATUSES[$status])) {
            $where[] = 'b.status = ?';
            $params[] = $status;
        }
        if ($upcomingOnly) {
            $where[] = 'b.booking_date >= CURDATE()';
        }
        $db = new Database();
        return $db->getAll(
            "SELECT b.*, s.title_et, m.name AS master_name, u.email AS account_email
             FROM bookings b
             JOIN services s ON s.id = b.service_id
             JOIN masters m ON m.id = b.master_id
             LEFT JOIN users u ON u.id = b.user_id "
            . ($where ? 'WHERE ' . implode(' AND ', $where) : '') .
            " ORDER BY b.booking_date, b.booking_time",
            $params
        );
    }

    public static function setStatus($id, $status) {
        if (!isset(self::STATUSES[$status])) {
            return false;
        }
        $db = new Database();
        $db->executeRun("UPDATE bookings SET status = ? WHERE id = ?", array($status, (int)$id));
        return true;
    }

    public static function deleteBooking($id) {
        $db = new Database();
        return $db->executeRun("DELETE FROM bookings WHERE id = ?", array((int)$id)) > 0;
    }
}
