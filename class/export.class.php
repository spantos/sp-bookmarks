<?php

/**
 * Exporting bookmarks into file
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
 * Exporting bookmarks into file
 *
 * @package    Export
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantovic
 * @license    http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 * @version    0.1.0
 */
class Export
{

    /**
     * Instance of BookmarksQuery class
     * @var object
     * @access private
     */
    private $bookmarks_query = null;

    /**
     * Containing all groups
     * @var array
     * @access private
     */
    private $groups = array();

    /**
     * Containing all bookmarks
     * @var array
     * @access private
     */
    private $all_bookmarks = array();

    /**
     * Logged user ID
     * @var int
     * @access private
     */
    private $id_user;

    /**
     * Temporary files for storing bookmarks
     * @var resource
     * @access private
     */
    private $temp_file = null;

    /**
     * Default name for export file
     * @var string
     * @access private
     */
    private $export_file_name;

    /**
     * Registering browsers for which it's possible to export bookmarks.
     * It's used to create a menu and for calling appropriate method for exporting bookmarks.
     *
     * array['fields']
     *          [fieldName]                      Defines the options for a field
     *                  ['menu_item']     string Menu item for exporting bookmarks
     *                  ['url_get']       string The value of the GET variable in the URL
     *                  ['export_method'] string Method for exporting bookmarks
     * @var array
     * @access private
     * @static
     */
    private static $register_browser = array(array(
        'menu_item' => 'Mozilla Firefox',
        'url_get' => 'mozilla',
        'export_method' => 'exportMozillaFirefox')
    );

    /**
     * Browser for which is required export the bookmarks
     * @var string
     * @access private
     */
    private $browser = null;

    /**
     * Constructor
     * @param int $id_user
     * @param string $browser
     * @access public
     */
    public function __construct($id_user, $browser)
    {
        $this->temp_file = tempnam('./tmp', 'data');
        $this->id_user = $id_user;
        $this->bookmarks_query = new BookmarksQuery;
        $this->groups = $this->bookmarks_query->getAllGroups($this->id_user);
        $this->all_bookmarks = $this->bookmarks_query->getAllBookmarks($this->id_user);
        $this->browser = $browser;
        $this->exportFile();
    }

    /**
     * Return array with registered browser for exporting bookmarks.
     *
     * @return array
     * @access public
     */
    public static function getRegisteredBrowser()
    {
        foreach (self::$register_browser as $browser) {
            if (array_key_exists('export_method', $browser)) {
                unset($browser['export_method']);
                $export_menu[] = $browser;
            } else {
                throw new Exception('Not defined method for exporting data.');
            }
        }
        return $export_menu;
    }

    /**
     * Exporting bookmarks for Mozilla Firefox.
     *
     * @return void
     * @access private
     */
    private function exportMozillaFirefox()
    {
        $export_bookmarks = "<!DOCTYPE NETSCAPE-Bookmark-file-1>" . PHP_EOL;
        $export_bookmarks .= '<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">' . PHP_EOL;
        $export_bookmarks .= '<TITLE>SP Bookmarks</TITLE>' . PHP_EOL;
        $export_bookmarks .= '<H1>SP Bookmarks</H1>' . PHP_EOL;
        $export_bookmarks .= '<DL><p>' . PHP_EOL;
        $fexp = fopen($this->temp_file, 'a');
        fputs($fexp, $export_bookmarks);
        unset($export_bookmarks);
        foreach ($this->groups as $group) {
            $group_date = strtotime($group['created']);
            $modified = strtotime($group['modified']);
            fwrite($fexp, "<DT><H3 ADD_DATE='{$group_date}' LAST_MODIFIED='{$modified}'>" . $group['name'] . "</H3>" . PHP_EOL);
            fwrite($fexp, '<DL><p>' . PHP_EOL);
            if (array_key_exists($group['id_group'], $this->all_bookmarks['id_group'])) {
                foreach ($this->all_bookmarks['id_group'][$group['id_group']] as $bookmark) {
                    $bookmark_date = strtotime($bookmark['created']);
                    $modified = strtotime($bookmark['modified']);
                    fwrite($fexp, "<DT><A HREF='{$bookmark['bookmark_url']}' ADD_DATE='{$bookmark_date}' LAST_MODIFIED='{$modified}'>{$bookmark['bookmark_name']}</A>" . PHP_EOL);
                    fwrite($fexp, "<DD>{$bookmark['bookmark_description']}" . PHP_EOL);
                }
            }
            fwrite($fexp, '</DL><p>' . PHP_EOL);
        }
        fwrite($fexp, '</DL><p>' . PHP_EOL);
        fclose($fexp);
        $this->export_file_name = 'Firefox_bookmarks.html';
    }

    /**
     * Exporting bookmarks for Internet Explorer.
     *
     * @return void
     * @access private
     */
    private function exportIE()
    {

    }

    /**
     * Exporting bookmarks for Google Chrome.
     *
     * @return void
     * @access private
     */
    private function exportGoogleChrome()
    {

    }

    /**
     * Export bookmarks file
     *
     * @return void
     * @access private
     * @throws Exception Attribute $register_browser is not array or is empty
     * @throws Exception If not defined method for exporting data
     */
    private function exportFile()
    {
        if (is_array(self::$register_browser) && !empty(self::$register_browser)) {
            foreach (self::$register_browser as $browser) {
                if (in_array($this->browser, $browser)) {
                    if (isset($browser['export_method']) && !empty($browser['export_method']) && method_exists($this, $browser['export_method'])) {
                        call_user_func(array($this, $browser['export_method']));
                    } else {
                        throw new Exception('Not defined method for exporting data.');
                    }
                }
            }
        } else {
            throw new Exception('Attribute Export::$register_browser is not an array or is empty.');
        }
        header('Cache-Control: no-cache');
        header('Cache-Control: no-store');
        header("Content-Type: application/html; charset=UTF-8");
        header("Content-Disposition: attachment; filename={$this->export_file_name}");
        header("Content-type: application/octet-stream");
        readfile($this->temp_file);
        unlink($this->temp_file);
    }
}
