<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Translator
{
    private static $initialized = false;
    private static $translations = array();

    public static function translate($key, $language = null) {
        if (!self::$initialized) self::init();
        if (!$language) $language = isset($_GET['_locale']) ? $_GET['_locale']: 'en';
        echo isset(self::$translations[$language][$key]) ? self::$translations[$language][$key]: $key;
    }

    public static function init()
    {
        $french = array(
            'return' => 'Retour',
            'an_exception_occured' => 'Une exception est survenue.',
            'end_upgrade_message' => 'La mise à jour est terminée.',
            'close' => 'Fermer',
            'download_log' => 'Télécharger les logs'
        );

        $en = array(
            'return' => 'Return',
            'an_exception_occured' => 'An exception occured.',
            'end_upgrade_message' => 'The upgrade is over.',
            'close' => 'Close',
            'download_log' => 'Download logs'
        );

        $es = array(
            'return' => 'Regresar',
            'an_exception_occured' => 'Se produjo una excepción.',
            'end_upgrade_message' => 'La actualización se ha completado.',
            'close' => 'Cerrar',
            'download_log' => 'Descargar el registro'
        );

        self::$translations = array(
            'fr' => $french,
            'en' => $en,
            'es' => $es
        );

        self::$initialized = true;
    }
}