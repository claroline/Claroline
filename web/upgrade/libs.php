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
        $en = array(
            'upgrade_tool' => 'Upgrade tool',
            'upgrade_steps' => 'Upgrade steps',
            'create_backup' => 'Backup creation',
            'activate_maintenance_mode' => 'Maintenance activation',
            'bundle_table_initialization' => 'Database initialization',
            'vendor_replacement' => 'Vendor replacement',
            'executing_migrations' => 'Upgrade execution',
            'remove_maintenance_mode' => 'Disabling maintenance',
            'start' => 'Start',
            'return' => 'Return',
            'close' => 'Close',
            'debug_mode' => 'Debug mode',
            'debug_mode_explanation' => 'The debug mode allows you to execute the update operations in an arbitrary order.'
        );

        $fr = array(
            'upgrade_tool' => 'Outil de mise à jour',
            'upgrade_steps' => 'Étapes de la mise à jour',
            'create_backup' => 'Creation d\'un backup',
            'activate_maintenance_mode' => 'Activation du mode maintenance',
            'bundle_table_initialization' => 'Initialisation de la base de donnée',
            'vendor_replacement' => 'Remplacement du dossier vendor',
            'executing_migrations' => 'Exécution de la mise à jour',
            'remove_maintenance_mode' => 'Désactivation du mode maintenance',
            'start' => 'Commencer',
            'return' => 'Retour',
            'close' => 'Fermer',
            'debug_mode' => 'Mode de debug',
            'debug_mode_explanation' => 'Le mode de debug vous permet d\'exécuter les opérations de mise à jour dans un ordre arbitraire.'
        );

        $es = array(
            'return' => 'Regresar',
            'close' => 'Cerrar'
        );

        self::$translations = array(
            'fr' => $fr,
            'en' => $en,
            'es' => $es
        );

        self::$initialized = true;
    }
}

class FileManager
{
    /**
     * http://stackoverflow.com/questions/1653771/how-do-i-remove-a-directory-that-is-not-empty
     * @param $dir
     * @return bool
     */
    public static function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }
}
