<?php

/**
 * Create and manage bookmark
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
 * Create and manage bookmark
 *
 * @package    Bookmark
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantovic
 * @license    http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 * @version    0.1.0
 */
class Bookmark
{

    /**
     * Instance of database
     * @var PDO
     * @access private
     */
    private $db = null;

    /**
     * After the method execution in this variable is stored information that is displayed to the user
     * @var string
     * @access private
     */
    private $status;

    /**
     * The name of the bookmark
     * @var string
     * @access private
     */
    private $bookmark_name;

    /**
     * URL address of the bookmark
     * @var string
     * @access private
     */
    private $bookmark_url;

    /**
     * Description of the bookmark
     * @var string
     * @access private
     */
    private $bookmark_description;

    /**
     * ID of the bookmark group
     * @var int
     * @access private
     */
    private $id_group;

    /**
     * User ID
     * @var int
     * @access private
     */
    private $id_user;

    /**
     * Creates a database instance
     * @access public
     */
    public function __construct()
    {
        $this->db = DataBase::dbConnect();
    }

    /**
     * Saves the bookmark in database
     *
     * @param string $name The name of the bookmark
     * @param string $url URL address of the bookmark
     * @param int $id_group ID of the bookmark group
     * @param int $id_user User ID
     * @param string $description Description of the bookmark
     * @return void                Depending on the results of the execution method,
     *                             the corresponding message will be recorded in the variable $status
     * @access public
     */
    public function saveBookmark($name, $url, $id_group, $id_user, $description = null)
    {
        $this->bookmark_name = $name;
        $this->bookmark_url = $url;
        $this->id_group = $id_group;
        $this->id_user = $id_user;
        $this->bookmark_description = $description;
        if (!$this->checkIfExists($this->bookmark_url, $this->id_user)) {
            try {
                $date = date(DATETIME_FORMAT);
                $db_request = $this->db->prepare("INSERT INTO bookmarks 
                                                (bookmark_name,bookmark_url,bookmark_description,id_group,id_user,created,modified)
                                                VALUES (:name,:url,:description,:id_group,:id_user,:created,:modified)");
                $db_request->bindParam(':name', $this->bookmark_name);
                $db_request->bindParam(':url', $this->bookmark_url);
                $db_request->bindParam(':description', $this->bookmark_description);
                $db_request->bindParam(':id_group', $this->id_group);
                $db_request->bindParam(':id_user', $this->id_user);
                $db_request->bindParam(':created', $date);
                $db_request->bindParam(':modified', $date);
                $db_request->execute();
                $this->status = 'The Bookmark is created';
            } catch (PDOException $e) {
                echo 'An error has occurred. Bookmarks isn\'t created.';
                DBErrorLog::loggingErrors($e);
            }
        } else {
            $this->status = 'Bookmark isn\'t crated. The URL already exists';
        }
    }

    /**
     * Takes a bookmark from the database
     *
     * @param int $id_bookmark Bookmark id
     * @param int $id_user User ID
     * @return mixed array|false   If there is a bookmark returns an array otherwise it returns false
     *                             and the corresponding message will be recorded in the variable $status
     * @access public
     */

    public function getBookmark($id_bookmark, $id_user)
    {
        try {
            $db_request = $this->db->prepare('SELECT * FROM bookmarks 
                                            WHERE id_bookmark=:id_bookmark AND id_user=:id_user LIMIT 1');
            $db_request->bindParam(':id_bookmark', $id_bookmark);
            $db_request->bindParam(':id_user', $id_user);
            $db_request->execute();
            if ($db_request->rowCount()) {
                $bookmark_data = $db_request->fetch(PDO::FETCH_ASSOC);
                return $bookmark_data;
            } else {
                $this->status = 'This bookmark not exists.';
                return false;
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
        }

    }

    /**
     * Delete bookmark
     *
     * @param int $id_bookmark Bookmark id
     * @param int $id_user User id
     * @return void                 Depending on the results of the execution method,
     *                              the corresponding message will be recorded in the variable $status
     * @access public
     */
    public function deleteBookmark($id_bookmark, $id_user)
    {
        try {
            $db_request = $this->db->prepare('DELETE FROM bookmarks 
                                            WHERE id_bookmark=:id_bookmark AND id_user=:id_user LIMIT 1');
            $db_request->bindParam(':id_bookmark', $id_bookmark);
            $db_request->bindParam(':id_user', $id_user);
            $db_request->execute();
            if ($db_request->rowCount()) {
                $this->status = 'Bookmark is deleted';
            } else {
                $this->status = 'An error has occurred. Bookmarks isn\'t deleted.';
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
        }
    }


    /**
     * Edit bookmark
     *
     * @param int $id_bookmark
     * @param int $id_user
     * @param string $bookmark_name
     * @param string $bookmark_url
     * @param int $id_group
     * @param string $bookmark_description
     * @return void                         Depending on the results of the execution method,
     *                                      the corresponding message will be recorded in the variable $status
     * @access public
     */
    public function editBookmark(
        $id_bookmark,
        $id_user,
        $bookmark_name,
        $bookmark_url,
        $id_group,
        $bookmark_description = null
    )
    {
        $modified = date(DATETIME_FORMAT);
        try {
            $db_request = $this->db->prepare('UPDATE bookmarks
                                            LEFT JOIN
                                            groups ON bookmarks.id_group=groups.id_group
                                            SET bookmark_name=:bookmark_name,
                                                bookmark_url=:bookmark_url,
                                                bookmarks.id_group=:id_group,
                                                bookmark_description=:bookmark_description,
                                                bookmarks.modified=:modified,
                                                groups.modified=:group_modified
                                            WHERE id_bookmark=:id_bookmark AND bookmarks.id_user=:id_user');
            $db_request->bindParam(':bookmark_name', $bookmark_name);
            $db_request->bindParam(':bookmark_url', $bookmark_url);
            $db_request->bindParam(':id_group', $id_group);
            $db_request->bindParam(':bookmark_description', $bookmark_description);
            $db_request->bindParam(':id_bookmark', $id_bookmark);
            $db_request->bindParam(':id_user', $id_user);
            $db_request->bindParam(':modified', $modified);
            $db_request->bindParam(':group_modified', $modified);
            $db_request->execute();
            if ($db_request->rowCount()) {
                $this->status = 'The Bookmark is changed.';
            } else {
                $this->status = 'Bookmark not changed.';
            }
        } catch (PDOException $e) {
            $this->status = 'An error has occurred. Try later.';
            DBErrorLog::loggingErrors($e);
        }
    }

    /**
     * Status of the method execution
     *
     * @return string Return status of the method execution
     * @access public
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Checking if bookmark exists
     *
     * @param string $url
     * @param int $id_user
     * @return boolean If there is bookmark returns true, otherwise returns false.
     * @access private
     */
    private function checkIfExists($url, $id_user)
    {
        try {
            $db_request = $this->db->prepare('SELECT bookmark_url FROM bookmarks WHERE bookmark_url=:bookmark_url 
                                            AND id_user=:id_user LIMIT 1');
            $db_request->bindParam(':bookmark_url', $url);
            $db_request->bindParam(':id_user', $id_user);
            $db_request->execute();
            if ($db_request->rowCount()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            DBErrorLog::loggingErrors($e);
            die('An error has occurred. Try later.');
        }
    }
}
