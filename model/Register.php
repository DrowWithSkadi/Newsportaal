<?php
// Kasutaja registreerimine
class Register {

    public static function registerUser() {
        $result = array(0 => false, 1 => t('reg_err'));
        if (!isset($_POST['save'])) {
            return $result;
        }
        if (!Auth::csrfValid()) {
            return array(0 => false, 1 => e(t('csrf_err')));
        }
        // Kaitse masinloodud kontode vastu: ühest IP-st piiratud arv kontosid
        if (!Auth::registrationAllowed()) {
            return array(0 => false, 1 => e(t('reg_err_limit')));
        }

        $errors   = array();
        $name     = trim(isset($_POST['name']) ? $_POST['name'] : '');
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm  = isset($_POST['confirm']) ? $_POST['confirm'] : '';

        if ($name === '') {
            $errors[] = t('err_required');
        }
        if (!$email) {
            $errors[] = t('reg_err_email');
        }
        if (mb_strlen($password) < 6) {
            $errors[] = t('reg_err_pass');
        }
        if ($password !== $confirm) {
            $errors[] = t('reg_err_match');
        }

        $db = new Database();
        if ($email && $db->getOne("SELECT id FROM users WHERE email = ?", array(strtolower($email)))) {
            $errors[] = t('reg_err_exists');
        }

        if (count($errors) > 0) {
            return array(0 => false, 1 => implode('<br>', array_map('e', $errors)));
        }

        $ok = $db->executeRun(
            "INSERT INTO users (username, email, password, status, registration_date) VALUES (?, ?, ?, 'user', CURDATE())",
            array(mb_substr($name, 0, 100), strtolower($email), password_hash($password, PASSWORD_DEFAULT))
        );
        if (!$ok) {
            return $result;
        }
        Auth::logRegistration();
        // uus kasutaja logitakse kohe sisse
        Auth::login($db->getOne("SELECT * FROM users WHERE id = ?", array((int)$db->lastInsertId())));
        return array(0 => true);
    }
}
