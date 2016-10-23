<?php

/**
 * Log database error
 *
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
 * @version    0.1.0
 */

/**
 * Log database error
 *
 * @package   DBErrorLog
 * @author    Slobodan Pantovic spbookmarks@gmail.com
 * @copyright 2016 Slobodan Pantovic
 * @license   http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 * @version   0.1.0
 */
class DBErrorLog
{

    /**
     *
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * Log database errors
     *
     * @param object $e PDOException object
     * @return void
     * @access public
     * @static
     */
    public static function loggingErrors($e)
    {
        if (is_object($e) && ($e instanceof PDOException)) {
            error_log(date(DATETIME_FORMAT) . '[' . $e->getFile() . '] ' . '[line:' . $e->getLine() . '] '
                . '[message:' . $e->getMessage() . ']' . PHP_EOL, 3, DB_ERROR_LOG_FILE);
        } else {
            error_log(date(DATETIME_FORMAT) .
                '[message: Argument 1 passed to DBErrorLog::logError() must be an instance of PDOException, ' .
                gettype($e) . ' given.' . PHP_EOL, 3, DB_ERROR_LOG_FILE);
        }
    }
}
