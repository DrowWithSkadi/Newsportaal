<?php
/**
 * Kasutaja sisselogimine, väljalogimine ja kaitse parooli ära arvamise vastu.
 * Sama klassi kasutavad nii avalik sait kui ka halduspaneel.
 *
 * Lukustus kehtib paarile e-post + IP-aadress: kui samast IP-st tehakse samale
 * e-postile MAX_ATTEMPTS ebaõnnestunud katset LOCK_MINUTES minuti jooksul, on see
 * paar LOCK_MINUTES minutit lukus. Nii ei saa võõras oma katsetega teise kasutaja
 * (nt administraatori) kontot kinni panna.
 *
 * Lisaks piiratakse kogu IP-aadressi: MAX_IP_ATTEMPTS ebaõnnestunud katset
 * LOCK_MINUTES minuti jooksul lukustab IP-aadressi – see takistab paroolide
 * proovimist paljude eri e-postidega (password spraying).
 */
class Auth {
    const MAX_ATTEMPTS    = 5;   // katset ühele e-postile ühest IP-st
    const MAX_IP_ATTEMPTS = 20;  // katset kokku ühest IP-st
    const LOCK_MINUTES    = 15;
    const MAX_REGISTRATIONS_PER_IP = 5; // uut kontot ühest IP-st LOCK_MINUTES jooksul

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

        $ip = self::ip();

        $minutes = self::lockMinutesLeft($email, $ip);
        if ($minutes > 0) {
            return array('ok' => false, 'error' => 'login_locked', 'minutes' => $minutes);
        }

        $db = new Database();
        $user = $db->getOne("SELECT * FROM users WHERE email = ?", array($email));
        if ($user && password_verify($password, $user['password'])) {
            // Õnnestunud sisselogimine nullib ainult selle IP katsed, mitte kõik
            $db->executeRun("DELETE FROM login_attempts WHERE email = ? AND ip = ?", array($email, $ip));
            self::login($user);
            return array('ok' => true);
        }

        // Ebaõnnestunud katse salvestatakse ka olematu e-posti puhul,
        // et ei saaks kontrollida, millised e-postid on registreeritud.
        $db->executeRun("INSERT INTO login_attempts (email, ip) VALUES (?, ?)", array($email, $ip));

        $left = self::MAX_ATTEMPTS - self::failedAttempts($email, $ip);
        if ($left <= 0 || self::ipAttempts($ip) >= self::MAX_IP_ATTEMPTS) {
            return array('ok' => false, 'error' => 'login_locked', 'minutes' => self::LOCK_MINUTES);
        }
        return array('ok' => false, 'error' => 'login_err', 'left' => $left);
    }

    // Kliendi IP-aadress
    public static function ip() {
        return isset($_SERVER['REMOTE_ADDR']) ? substr((string)$_SERVER['REMOTE_ADDR'], 0, 45) : '';
    }

    // Ebaõnnestunud katsed sellele e-postile sellest IP-st (viimase LOCK_MINUTES jooksul)
    private static function failedAttempts($email, $ip) {
        $db = new Database();
        $row = $db->getOne(
            "SELECT COUNT(*) AS c FROM login_attempts
             WHERE email = ? AND ip = ? AND attempted_at > NOW() - INTERVAL " . self::LOCK_MINUTES . " MINUTE",
            array($email, $ip)
        );
        return (int)$row['c'];
    }

    // Ebaõnnestunud katsed kokku sellest IP-st (kaitse paljude e-postide proovimise vastu)
    private static function ipAttempts($ip) {
        $db = new Database();
        $row = $db->getOne(
            "SELECT COUNT(*) AS c FROM login_attempts
             WHERE ip = ? AND attempted_at > NOW() - INTERVAL " . self::LOCK_MINUTES . " MINUTE",
            array($ip)
        );
        return (int)$row['c'];
    }

    // Mitu minutit on lukustuse lõpuni (0 = pole lukus)
    public static function lockMinutesLeft($email, $ip = null) {
        $ip = $ip === null ? self::ip() : $ip;
        $byEmail = self::failedAttempts($email, $ip) >= self::MAX_ATTEMPTS;
        $byIp    = self::ipAttempts($ip) >= self::MAX_IP_ATTEMPTS;
        if (!$byEmail && !$byIp) {
            return 0;
        }

        $db = new Database();
        // lukustus algab viimasest katsest
        if ($byEmail) {
            $row = $db->getOne(
                "SELECT TIMESTAMPDIFF(SECOND, NOW(), MAX(attempted_at) + INTERVAL " . self::LOCK_MINUTES . " MINUTE) AS s
                 FROM login_attempts WHERE email = ? AND ip = ?",
                array($email, $ip)
            );
        } else {
            $row = $db->getOne(
                "SELECT TIMESTAMPDIFF(SECOND, NOW(), MAX(attempted_at) + INTERVAL " . self::LOCK_MINUTES . " MINUTE) AS s
                 FROM login_attempts WHERE ip = ?",
                array($ip)
            );
        }
        return max(1, (int)ceil(((int)$row['s']) / 60));
    }

    /**
     * Kas sellest IP-st tohib veel uue konto luua?
     * Takistab tuhandete kontode automaatset loomist.
     */
    public static function registrationAllowed() {
        $db = new Database();
        $row = $db->getOne(
            "SELECT COUNT(*) AS c FROM register_attempts
             WHERE ip = ? AND attempted_at > NOW() - INTERVAL " . self::LOCK_MINUTES . " MINUTE",
            array(self::ip())
        );
        return (int)$row['c'] < self::MAX_REGISTRATIONS_PER_IP;
    }

    public static function logRegistration() {
        $db = new Database();
        $db->executeRun("INSERT INTO register_attempts (ip) VALUES (?)", array(self::ip()));
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
