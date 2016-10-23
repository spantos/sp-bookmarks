<?php

/**
 * Bookmarks query
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
 * Bookamrks query
 *
 * @package    BookmarksQuery
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantovic
 * @license    http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 * @version    0.1.0
 */
class BookmarksQuery
{

    /**
     * Instance of database
     * @var PDO
     * @access private
     */
    private $db;

    /**
     * Contains data after searching
     * @var array
     * @access private
     */
    private $search_results = array();

    /**
     * Words for searching.
     * @var string
     * @access private
     */
    private $search_word;

    /**
     * Creates a database instance
     *
     * @access public
     */
    public function __construct()
    {
        $this->db = DataBase::dbConnect();
    }

    /**
     * Retrieving all groups from the database for the logged user
     *
     * @param int $id_user
     * @return array
     * @access public
     */
    public function getAllGroups($id_user)
    {
        try {
            $db_request = $this->db->prepare('SELECT id_group,name,created,modified FROM groups 
                                            WHERE id_user=:id_user ORDER BY name ASC');
            $db_request->bindParam(':id_user', $id_user);
            $db_request->execute();
            $res = $db_request->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
        }
        return $res;
    }

    /**
     * Retrieving all the bookmarks for the required group
     *
     * @param int $id_user
     * @param int $id_group
     * @return array
     * @access public
     */
    public function getBookmarks($id_user, $id_group)
    {
        try {
            $db_request = $this->db->prepare('SELECT id_bookmark,bookmark_name,bookmark_url,bookmark_description,created
                                            FROM bookmarks WHERE id_user=:id_user AND id_group=:id_group 
                                            ORDER BY bookmark_name ASC');
            $db_request->bindParam(':id_user', $id_user);
            $db_request->bindParam(':id_group', $id_group);
            $db_request->execute();
            $res = $db_request->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
        }
        if (!$res) {
            return false;
        } else {
            return $res;
        }
    }

    /**
     * Retrieving all bookmarks from the database for the logged user
     *
     * @param int $id_user
     * @return array
     * @access public
     */
    public function getAllBookmarks($id_user)
    {
        try {
            $db_request = $this->db->prepare('SELECT id_bookmark,bookmark_name,bookmark_url,
                                              bookmark_description,id_group,created,modified 
                                              FROM bookmarks WHERE id_user=:id_user 
                                              ORDER BY bookmark_name ASC');
            $db_request->bindParam(':id_user', $id_user);
            $db_request->execute();
            if (!$db_request->rowCount()) {
                return false;
            }
            while ($row = $db_request->fetch(PDO::FETCH_ASSOC)) {
                $bookmarks['id_group'][$row['id_group']][] = $row;
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
        }
        return $bookmarks;
    }

    /**
     * Bookmark search
     *
     * @param int $id_user
     * @param string $data Words for searching.
     * @return mixed array|string  If an error occurs when you search returns string 'error'
     * @access public
     */
    public function bookmarksSearch($id_user, $data)
    {
        try {
            $data = $this->sanitizeSearchInput($data);
            $search_text = explode(" ", $data);
            $db_request = $this->db->prepare("SELECT * FROM bookmarks
                                            WHERE (bookmark_name LIKE :bookmark_name 
                                            OR bookmark_url LIKE :bookmark_url 
                                            OR bookmark_description LIKE :bookmark_description)
                                            AND (id_user = :id_user)");
            $db_request->bindParam(':bookmark_name', $this->search_word);
            $db_request->bindParam(':bookmark_url', $this->search_word);
            $db_request->bindParam(':bookmark_description', $this->search_word);
            $db_request->bindParam(':id_user', $id_user);
            foreach ($search_text as $search_word) {
                $this->search_word = '%' . $search_word . '%';
                $db_request->execute();
                if ($db_request->rowCount()) {
                    $this->search_results[] = $db_request->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
            return $this->search_results = 'error';
        }
        return $this->search_results;
    }

    /**
     * Sanitize input data
     *
     * @param string $data
     * @return string
     * @access private
     */
    private function sanitizeSearchInput($data)
    {
        $data = trim($data);
        $data = filter_var($data, FILTER_SANITIZE_STRING);
        return $data;
    }
}
