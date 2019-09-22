<?php
DBC::init();
DBC::setConnectionID(1);
DBC::setServer('localhost');
DBC::setUser('user');
DBC::setPassword('pass');
DBC::setDatabase('db_name');
DBC::setCharset("utf8");

abstract class DBC
{
    private static $db_server = array();
    private static $db_database = array();
    private static $db_user = array();
    private static $db_passwd = array();
    private static $db_charset = "";
    private static $conn_id = 1;
    private static $conn_list = array();
    
    private static $debug;
    private static $connection;
    private static $openconnection;
    private static $has_error;
    private static $errors;
    
    public static function init()
    {
        self::reset();
    }
    
    public static function reset()
    {
        self::$connection = array();
        self::$openconnection = array();
        self::setConnectionID(self::$conn_id);
        
        self::$db_server[self::$conn_id] = "";
        self::$db_database[self::$conn_id] = "";
        self::$db_user[self::$conn_id] = "";
        self::$db_passwd[self::$conn_id] = "";
        self::$debug = false;
        self::$has_error = false;
        self::$errors = array();
    }
    
    public static function isConnected()
    {
        if (self::$connection[self::$conn_id] && is_object(self::$connection[self::$conn_id])) {
            self::$openconnection[self::$conn_id] = true;
            return self::$openconnection[self::$conn_id];
        } else {
            self::$openconnection[self::$conn_id] = true;
            return false;
        }
    }
    
    public static function getConnection()
    {
        if (!self::isConnected()) {
            array_push(self::$errors, sprintf('DBC Error (%s, %s): Database connection not open!', __LINE__, basename(__FILE__)));
            return null;
        }
        return self::$connection[self::$conn_id];
    }
    
    public static function hasError()
    {
        if (!self::isConnected()) {
            self::$has_error = true;
            array_push(self::$errors, sprintf('DBC Error (%s, %s): No connection has been created!', __LINE__, basename(__FILE__)));
        }
            
        return self::$has_error;
    }
    
    public static function check()
    {
        if (self::$connection[self::$conn_id] && is_object(self::$connection[self::$conn_id])) {
            //connections exists. Do nothing.
        } else {
            self::create();
            if (strlen(self::$db_charset) > 1) {
                self::qq("SET NAMES ".self::$db_charset.";");
            }
        }
    }
    
    public static function create($override = false)
    {
        if (!self::isConnected() || $override) {
            self::$connection[self::$conn_id] = new mysqli(self::$db_server[self::$conn_id], self::$db_user[self::$conn_id], self::$db_passwd[self::$conn_id], self::$db_database[self::$conn_id]);
            self::$openconnection[self::$conn_id] = true;
            if (mysqli_connect_errno()) {
                self::$openconnection[self::$conn_id] = false;
                array_push(self::$errors, sprintf(
                    'DBC Error (%s, %s): Database connection failed, %s!',
                    __LINE__,
                    basename(__FILE__),
                    mysqli_connect_error()
                ));
                self::$has_error = true;
            } else {
                self::$has_error = false;
            }
        } else {
            array_push(self::$errors, sprintf('DBC Error (%s, %s): Connection already open!', __LINE__, basename(__FILE__)));
        }
    }
    
    public static function st($query)
    {
        $statement = null;
        self::check();
        if (self::$isConnected()) {
            $statement = self::$connection[self::$conn_id]->prepare($query);
            if (!$statement || self::$connection[self::$conn_id]->errno) {
                array_push(self::$errors, sprintf(
                    'DBC Error (%s, %s): Statement error, %s',
                    __LINE__,
                    basename(__FILE__),
                    self::$connection[self::$conn_id]->error
                ));
                
                self::$has_error = true;
            } else {
                self::$has_error = false;
            }
        }
        return $statement;
    }
    
    public static function stError($statement)
    {
        self::check();
        if (self::$isConnected() && is_object($statement) && ($statement->errno)) {
            if ($statement->errno) {
                array_push(self::$errors, sprintf(
                    'DBC Error (%s, %s): Statement error, %s',
                    __LINE__,
                    basename(__FILE__),
                    $statement->error
                ));

                self::$has_error = true;
            } else {
                self::$has_error = false;
            }
                
            return true;
        }
        return false;
    }
    
    public static function lastID()
    {
        self::check();
        if (self::$connection[self::$conn_id]) {
            return self::$connection[self::$conn_id]->insert_id;
        }
            
        return 0;
    }
    
    public static function q($query)
    {
        self::check();
        if (!self::isConnected()) {
            return false;
        }
        
        if ($st = self::$connection[self::$conn_id]->prepare($query)) {
            if (func_num_args() > 1) {
                $x = func_get_args();
                $args = array_merge(
                    array(func_get_arg(1)),
                    array_slice($x, 2)
                );
                $args_ref = array();
                foreach ($args as $k => &$arg) {
                    $args_ref[$k] = &$arg;
                }
                call_user_func_array(array($st, 'bind_param'), $args_ref);
            }
            $st->execute();
 
            if ($st->errno) {
                self::$has_error = true;
                array_push(self::$errors, sprintf(
                    'DBC Error (%s, %s): Statement error, %s',
                    __LINE__,
                    basename(__FILE__),
                    $st->error
                ));
                 
                if (self::$debug) {
                    debug_print_backtrace();
                }
                return false;
            }
 
            if ($st->affected_rows > -1) {
                self::$has_error = false;
                return $st->affected_rows;
            }
            
            $params = array();
            $meta = $st->result_metadata();
            while ($field = $meta->fetch_field()) {
                $params[] = &$row[$field->name];
            }
            call_user_func_array(array($st, 'bind_result'), $params);
 
            $result = array();
            while ($st->fetch()) {
                $r = array();
                foreach ($row as $key => $val) {
                    $r[$key] = $val;
                }
                $result[] = $r;
            }
            $st->close();
            self::$has_error = false;
            return $result;
        } else {
            array_push(self::$errors, sprintf(
                'DBC Error (%s, %s): Quick qeury error, %s',
                __LINE__,
                basename(__FILE__),
                self::$connection[self::$conn_id]->error
            ));
                     
            self::$has_error = true;
            
            if (self::$debug) {
                debug_print_backtrace();
            }
                
            return false;
        }
    }
    
    public static function dbsql($query)
    {
        self::check();
        $result = null;
        
        if (self::isConnected()) {
            $result = self::$connection[self::$conn_id]->query($query);
            if (self::$connection[self::$conn_id]->errno) {
                array_push(self::$errors, sprintf(
                    'DBC Error (%s, %s): Query error, %s',
                    __LINE__,
                    basename(__FILE__),
                    self::$connection[self::$conn_id]->error
                ));
            }
        }
        return $result;
    }
    
    public static function dbfetch($results)
    {
        self::check();
        $arr = null;
        
        if (self::isConnected() && is_object($results)) {
            $arr = $results->fetch_array(MYSQLI_BOTH);
        
            if (self::$connection[self::$conn_id]->errno) {
                array_push(self::$errors, sprintf(
                    'DBC Error (%s, %s): dbfetch error, %s',
                    __LINE__,
                    basename(__FILE__),
                    self::$connection[self::$conn_id]->error
                ));
            }
        }
        return $arr;
    }
    
    public static function dbrows($results)
    {
        self::check();
        $res = null;
        
        if (self::isConnected() && is_object($results)) {
            $res = $results->num_rows;
        
            if (self::$connection[self::$conn_id]->errno) {
                array_push(self::$errors, sprintf(
                    'DBC Error (%s, %s): dbfetch error, %s',
                    __LINE__,
                    basename(__FILE__),
                    self::$connection[self::$conn_id]->error
                ));
            }
        }
        return $res;
    }
    
    public static function dbfree($results)
    {
        self::check();
        $res = null;
        
        if (self::isConnected() && is_object($results)) {
            $res = $results->free_result();
        
            if (self::$connection[self::$conn_id]->errno) {
                array_push(self::$errors, sprintf(
                    'DBC Error (%s, %s): dbfetch error, %s',
                    __LINE__,
                    basename(__FILE__),
                    self::$connection[self::$conn_id]->error
                ));
            }
        }
        return $res;
    }

    public static function qq($query)
    {
        self::check();
        $arr = array();
        $results = self::dbsql($query);
        while (($rows = self::dbfetch($results)) != null) {
            $arr[] = $rows;
        }
        
        return $arr;
    }
    
    public static function getErrors()
    {
        return self::$errors;
    }
    
    public static function printErrors()
    {
        foreach (self::$errors as $e) {
            printf('<li>%s</li>', $e);
        }
    }
    
    public static function close()
    {
        self::$openconnection[self::$conn_id] = false;
        if (self::$connection[self::$conn_id]) {
            self::$connection[self::$conn_id]->close();
            self::$connection[self::$conn_id] = null;

            self::$openconnection[self::$conn_id] = false;
        }
    }
    
    public static function closeAll()
    {
        foreach (self::$conn_list as $conn_id) {
            DBC::setConnectionID($conn_id);
            self::close();
        }
    }
    
    public static function getUser()
    {
        return self::$db_user[self::$conn_id];
    }
    
    public static function getServer()
    {
        return self::$db_server[self::$conn_id];
    }
    
    public static function getDatabase()
    {
        return self::$db_database[self::$conn_id];
    }
    
    public static function setUser($value)
    {
        self::$db_user[self::$conn_id] = $value;
    }
    
    public static function setPassword($value)
    {
        self::$db_passwd[self::$conn_id] = $value;
    }
    
    public static function setServer($value)
    {
        self::$db_server[self::$conn_id] = $value;
    }
    
    public static function setDatabase($value)
    {
        self::$db_database[self::$conn_id] = $value;
    }
    
    public static function setDebug($debug)
    {
        self::$debug = $debug;
    }
    
    public static function setCharset($charset)
    {
        self::$db_charset = $charset;
    }
    
    public static function getCharset()
    {
        return self::$db_charset;
    }
    
    public static function setConnectionID($identifier)
    {
        if ($identifier < 1) {
            $identifier = 1;
        }
                    
        self::$conn_id = $identifier;
        
        if (!isset(self::$openconnection[self::$conn_id])) {
            self::$openconnection[self::$conn_id] = null;
        }
            
        if (!isset(self::$connection[self::$conn_id])) {
            self::$connection[self::$conn_id] = null;
        }
            
        if (!isset(self::$db_server[self::$conn_id])) {
            self::$db_server[self::$conn_id] = "";
        }
            
        if (!isset(self::$db_database[self::$conn_id])) {
            self::$db_database[self::$conn_id] = "";
        }
            
        if (!isset(self::$db_user[self::$conn_id])) {
            self::$db_user[self::$conn_id] = "";
        }
            
        if (!isset(self::$db_passwd[self::$conn_id])) {
            self::$db_passwd[self::$conn_id] = "";
        }
            
        if (!in_array(self::$conn_id, self::$conn_list)) {
            self::$conn_list[] = self::$conn_id;
        }
    }
    
    public static function getConnectionID()
    {
        return self::$conn_id;
    }
    
    public static function dbescape($value)
    {
        self::check();
        $result = null;
        
        if (self::isConnected()) {
            $result = mysqli_real_escape_string(self::$connection[self::$conn_id], $value);
            return $result;
        }
        return null;
    }
}
