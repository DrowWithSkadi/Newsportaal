<?php
/**
 * Mitmekeelsus (ET / RU).
 * Keel valitakse parameetriga ?lang=et|ru ja salvestatakse sessiooni.
 * Liidese tekstid on failides lang/et.php ja lang/ru.php,
 * andmebaasi sisu on veergudes *_et ja *_ru.
 */
class Lang {
    const SUPPORTED = array('et', 'ru');
    const DEFAULT_LANG = 'et';

    private static $lang = self::DEFAULT_LANG;
    private static $strings = array();

    public static function init() {
        if (isset($_GET['lang']) && in_array($_GET['lang'], self::SUPPORTED, true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        self::$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : self::DEFAULT_LANG;
        self::$strings = require __DIR__ . '/../lang/' . self::$lang . '.php';
    }

    public static function current() {
        return self::$lang;
    }

    // Liidese tekst võtme järgi
    public static function t($key) {
        return isset(self::$strings[$key]) ? self::$strings[$key] : $key;
    }

    // Andmebaasi välja tõlge: field($row, 'title') -> title_et või title_ru
    public static function field($row, $base) {
        $key = $base . '_' . self::$lang;
        if (isset($row[$key]) && $row[$key] !== '') {
            return $row[$key];
        }
        return isset($row[$base . '_' . self::DEFAULT_LANG]) ? $row[$base . '_' . self::DEFAULT_LANG] : '';
    }

    // Praeguse lehe link teises keeles
    public static function switchUrl($lang) {
        $query = $_GET;
        $query['lang'] = $lang;
        $path = explode('?', $_SERVER['REQUEST_URI'])[0];
        return htmlspecialchars($path . '?' . http_build_query($query));
    }
}

// Lühikesed abifunktsioonid vaadetes kasutamiseks
function t($key) {
    return Lang::t($key);
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
