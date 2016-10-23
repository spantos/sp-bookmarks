<?php

/**
 * Database connection
 *
 * PHP version 5
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantovic
 * @license    http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 */


/**
 * Database connection
 *
 * @package   DataBase
 * @author    Slobodan Pantovic spbookmarks@gmail.com
 * @copyright 2016 Slobodan Pantovic
 * @license   http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 */
class DataBase
{

    /**
     * Instance of database
     * @var PDO
     * @access private
     * @static
     */
    private static $pdo = null;

    /**
     *
     * @access private
     */
    private function __construct()
    {

    }

    /**
     *
     * @return void
     * @access private
     */
    private function __clone()
    {

    }

    /**
     * Create database connection
     *
     * @return object Return instance of database
     * @access public
     * @static
     */
    public static function dbConnect()
    {
        if (!isset(self::$pdo)) {
            try {
                @self::$pdo = new PDO("mysql:host=" . DB_HOST . "; dbname=" . DB_NAME .
                    "; charset=utf8", DB_USER, DB_PASSWORD);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                return self::$pdo;
            } catch (PDOException $e) {
                error_log(date(DATETIME_FORMAT) . '[' . $e->getFile() . '] ' . '[line:' . $e->getLine() . '] '
                    . '[message:' . $e->getMessage() . ']' . PHP_EOL, 3, DB_ERROR_LOG_FILE);
                die('Try again, the application is not available.');
            }
        } else {
            return self::$pdo;
        }
    }
}
