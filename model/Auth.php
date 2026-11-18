<?php
/**
 * Kasutaja sisselogimine, väljalogimine ja kaitse parooli ära arvamise vastu.
 * Sama klassi kasutavad nii avalik sait kui ka halduspaneel.
 *
 * Lukustus: kui samale e-postile tehakse MAX_ATTEMPTS ebaõnnestunud katset
 * LOCK_MINUTES minuti jooksul, ei saa sellega LOCK_MINUTES minutit sisse logida.
 */
class Auth {
    const MAX_ATTEMPTS = 5;
    const LOCK_MINUTES = 15;

    // Sisselogitud kasutaja andmed või null
    public static function user() {
        if (!isset($_SESSION['userId'], $_SESSION['sessionId'])) {
            return null;
        }
        return array(
            'id'     => (int)$_SESSION['userId'],
            'name'   => $_SESSION['name'],
            'email'  => isset($_SESSION['email']) ? $_SESSION['email'] : '',
            'status' => $_SESSION['status'],
        );
    }

    public static function check() {
        return self::user() !== null;
    }

    public static function id() {
        $u = self::user();
        return $u ? $u['id'] : 0;
    }

    /**
     * Sisselogimise katse.
     * Tagastab array('ok' => true) või
     * array('ok' => false, 'error' => 'login_err'|'login_locked', 'left' => katseid jäänud, 'minutes' => ootamise aeg)
     */
    public static function attempt($email, $password) {
        $email = strtolower(trim((string)$email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return array('ok' => false, 'error' => 'login_err', 'left' => null);
        }

        $minutes = self::lockMinutesLeft($email);
        if ($minutes > 0) {
            return array('ok' => false, 'error' => 'login_locked', 'minutes' => $minutes);
        }

        $db = new Database();
        $user = $db->getOne("SELECT * FROM users WHERE email = ?", array($email));
        if ($user && password_verify($password, $user['password'])) {
            $db->executeRun("DELETE FROM login_attempts WHERE email = ?", array($email));
            self::login($user);
            return array('ok' => true);
        }

        // Ebaõnnestunud katse salvestatakse ka olematu e-posti puhul,
        // et ei saaks kontrollida, millised e-postid on registreeritud.
        $db->executeRun(
            "INSERT INTO login_attempts (email, ip) VALUES (?, ?)",
            array($email, isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')
        );
        $left = self::MAX_ATTEMPTS - self::failedAttempts($email);
        if ($left <= 0) {
            return array('ok' => false, 'error' => 'login_locked', 'minutes' => self::LOCK_MINUTES);
        }
        return array('ok' => false, 'error' => 'login_err', 'left' => $left);
    }

    // Ebaõnnestunud katsete arv viimase LOCK_MINUTES minuti jooksul
    private static function failedAttempts($email) {
        $db = new Database();
        $row = $db->getOne(
            "SELECT COUNT(*) AS c FROM login_attempts
             WHERE email = ? AND attempted_at > NOW() - INTERVAL " . self::LOCK_MINUTES . " MINUTE",
            array($email)
        );
        return (int)$row['c'];
    }

    // Mitu minutit on lukustuse lõpuni (0 = pole lukus)
    public static function lockMinutesLeft($email) {
        if (self::failedAttempts($email) < self::MAX_ATTEMPTS) {
            return 0;
        }
        $db = new Database();
        // lukustus algab viimasest katsest
        $row = $db->getOne(
            "SELECT TIMESTAMPDIFF(SECOND, NOW(), MAX(attempted_at) + INTERVAL " . self::LOCK_MINUTES . " MINUTE) AS s
             FROM login_attempts WHERE email = ?",
            array($email)
        );
        return max(1, (int)ceil(((int)$row['s']) / 60));
    }

    // Kasutaja andmed sessiooni (kasutatakse ka pärast registreerimist)
    public static function login($user) {
        session_regenerate_id(true);
        $_SESSION['sessionId'] = session_id();
        $_SESSION['userId']    = (int)$user['id'];
        $_SESSION['name']      = $user['username'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['status']    = $user['status'];
    }

    public static function logout() {
        unset($_SESSION['sessionId'], $_SESSION['userId'], $_SESSION['name'], $_SESSION['email'], $_SESSION['status']);
        session_regenerate_id(true);
    }

    //------------------------- CSRF-kaitse POST-vormidele
    public static function csrfToken() {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf'];
    }

    public static function csrfField() {
        return '<input type="hidden" name="csrf" value="' . self::csrfToken() . '">';
    }

    public static function csrfValid() {
        return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
    }

    // Turvaline tagasisuunamise aadress (ainult saidi sisesed suhtelised lingid)
    public static function safeReturn($url, $default = './') {
        $url = (string)$url;
        if ($url === '' || !preg_match('~^[A-Za-z][A-Za-z0-9_]*(\?[^\s<>"]*)?$~', $url)) {
            return $default;
        }
        return $url;
    }
}
