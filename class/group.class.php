<?php

/**
 * Creating and manipulating groups of bookmarks
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
 * Creating and manipulating groups of bookmarks
 *
 * @package    Group
 * @author     Slobodan Pantovic spbookmarks@gmail.com
 * @copyright  2016 Slobodan Pantoivc
 * @license    http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 2.1
 * @version    0.1.0
 */
class Group
{

    /**
     * After the method execution in this variable is stored information that is displayed to the user
     * @var string
     * @access private
     */
    private $status;

    /**
     * User ID
     * @var int
     * @access private
     */
    private $id_user = null;

    /**
     * Name of the bookmarks group
     * @var string
     * @access private
     */
    private $group_name;

    /**
     * Instance of database
     * @var PDO
     * @access private
     */
    private $db = null;

    /**
     * Group ID
     * @var int
     * @access private
     */
    private $id_group;

    /**
     * Constructor
     *
     * Create database instance
     *
     * @access public
     */
    public function __construct()
    {
        $this->db = DataBase::dbConnect();
    }

    /**
     * Create a group of bookmarks
     *
     * @param int $id_user
     * @param string $group_name
     * @return void
     * @access public
     */
    public function createGroup($id_user, $group_name)
    {
        $this->id_user = $id_user;
        $this->group_name = $group_name;
        $data = array();
        $data[] = array('column' => 'name', 'value' => $group_name);
        $data[] = array('column' => 'id_user', 'value' => $id_user);
        if ($this->checkIfGroupExists($data)) {
            $this->status = 'The group already exists.';
        } else {
            try {
                $date = date(DATETIME_FORMAT);
                $db_request = $this->db->prepare('INSERT INTO groups (id_user,name,created,modified)
                                                VALUES (:user_id,:name,:created,:modified)');
                $db_request->bindParam(':user_id', $this->id_user);
                $db_request->bindParam(':name', $this->group_name);
                $db_request->bindParam(':created', $date);
                $db_request->bindParam(':modified', $date);
                $db_request->execute();
                $this->status = "Created the group.";
            } catch (PDOException $e) {
                $this->status = "An error has occurred. The group is not created.";
                DBErrorLog::loggingErrors($e);
            }
        }
    }

    /**
     * Status of method execution
     *
     * @return string Return the message that will be displayed to the user
     * @access public
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Editing of the group name
     *
     * @param int $id_user
     * @param string $new_group_name
     * @param int $id_group
     * @return void
     * @access public
     */
    public function editGroupName($id_user, $new_group_name, $id_group)
    {
        $this->id_user = $id_user;
        $this->group_name = $new_group_name;
        $this->id_group = $id_group;
        $data = array();
        $data[] = array('column' => 'name', 'value' => $new_group_name);
        $data[] = array('column' => 'id_user', 'value' => $id_user);
        $modified = date(DATETIME_FORMAT);
        if ($this->checkIfGroupExists($data)) {
            $this->status = 'The group already exists.';
        } else {
            $data = array();
            $data[] = array('column' => 'id_group', 'value' => $id_group);
            $data[] = array('column' => 'id_user', 'value' => $id_user);
            if (!$this->checkIfGroupExists($data)) {
                $this->status = 'The group for edit don\'t exists.';
            } else {
                try {
                    $db_request = $this->db->prepare('UPDATE groups SET name=:new_group_name,modified=:modified
                                                    WHERE id_user=:id_user AND id_group=:id_group');
                    $db_request->bindParam('id_user', $this->id_user);
                    $db_request->bindParam('new_group_name', $this->group_name);
                    $db_request->bindParam('id_group', $this->id_group);
                    $db_request->bindParam(':modified', $modified);
                    $db_request->execute();
                    $this->status = "Name of the group is changed.";
                } catch (PDOException $e) {
                    $this->status = "An error has occurred. Not changed the name of the group.";
                    DBErrorLog::loggingErrors($e);
                }
            }
        }
    }

    /**
     * Deleting the bookmarks group.
     *
     * @param int $id_user
     * @param int $id_group
     * @return void
     * @access public
     */
    public function deleteGroup($id_user, $id_group)
    {
        $this->id_user = $id_user;
        $this->id_group = $id_group;
        $data = array();
        $data[] = array('column' => 'id_group', 'value' => $id_group);
        $data[] = array('column' => 'id_user', 'value' => $id_user);
        if (!$this->checkIfGroupExists($data)) {
            $this->status = 'The group don\'t exists!';
        } else {
            try {
                $db_request = $this->db->prepare('DELETE groups.*, bookmarks.*
                                                FROM groups
                                                LEFT JOIN bookmarks ON groups.id_group = bookmarks.id_group
                                                WHERE groups.id_group=:id_group AND groups.id_user=:id_user');
                $db_request->bindParam('id_user', $this->id_user);
                $db_request->bindParam('id_group', $this->id_group);
                $db_request->execute();
                if ($db_request->rowCount()) {
                    $this->status = "Group is deleted.";
                } else {
                    $this->status = "An error has occurred. Not a deleted group.";
                }
            } catch (PDOException $e) {
                $this->status = "An error has occurred. Not a deleted group.";
                DBErrorLog::loggingErrors($e);
            }
        }
    }

    /**
     * Checks if the group already exists
     *
     * @param array $data
     * @return boolean Returns true if the group exists otherwise false.
     * @access private
     */
    private function checkIfGroupExists($data)
    {
        $query = 'SELECT name FROM groups WHERE ';
        if (is_array($data)) {
            for ($i = 0, $n = count($data); $i < $n; $i++) {
                if (key_exists('column', $data[$i]) && key_exists('value', $data[$i])) {
                    $query = $query . $data[$i]['column'] . "=:" . $data[$i]['column'] . (($i + 1) <
                        $n ? ' AND ' : "");
                } else {
                    throw new Exception('They are not the appropriate fields in an array.');
                }
            }
            $query = $query . " LIMIT 1";
        } else {
            throw new Exception('Variable $data must provide an array.');
        }
        $db_request = $this->db->prepare($query);
        for ($i = 0, $n = count($data); $i < $n; $i++) {
            $db_request->bindParam($data[$i]['column'], $data[$i]['value']);
        }
        try {
            $db_request->execute();
            if ($db_request->rowCount()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->status = "An error has occurred. Try again.";
            DBErrorLog::loggingErrors($e);
        }
    }
}
