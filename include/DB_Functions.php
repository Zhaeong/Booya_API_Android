<?php

/*
create database android_api 

 
create table users(
   id int(11) primary key auto_increment,
   unique_id varchar(23) not null unique,
   username varchar(50) not null,
   email varchar(100) not null unique,
   encrypted_password varchar(80) not null,
   salt varchar(10) not null,
   created_at datetime,
   updated_at datetime null
); 


create table services(
	id int(32) primary key auto_increment,
	name varchar(50) not null,
	description varchar(50),
	created_at datetime
)

 */

class DB_Functions {

    private $conn;

    // constructor
    function __construct() {
        require_once 'DB_Connect.php';
        // connecting to database
        $db = new Db_Connect();
        $this->conn = $db->connect();
    }

    // destructor
    function __destruct() {
        
    }

    /**
     * Storing new user
     * returns user details
     */
    public function storeUser($name, $email, $password) {
		
        $uuid = uniqid('', true);
		

		$hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt
		

		$stmt = 'INSERT INTO users(unique_id, username, email, encrypted_password, salt, created_at) 
				VALUES ('  . 
				"'" . $uuid .  "'" . 
				',' . "'" . $name . "'" .
				',' . "'" . $email . "'" .
				',' . "'" . $encrypted_password . "'" .
				',' . "'" . $salt . "'" .
				',' . 'NOW()'
				.')';
		//echo $stmt;
		
        
		$result = mysqli_query($this->conn, $stmt);		
		
        // check for successful store
		
        if ($result) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            return $user;
        } else {
			echo "Error: " . $stmt . "<br>" . mysqli_error($this->conn);
            return false;
        }
		
    }

    /**
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password) {

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");

        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // verifying user password
            $salt = $user['salt'];
            $encrypted_password = $user['encrypted_password'];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct
                return $user;
            }
        } else {
            return NULL;
        }
    }

    /**
     * Check user is existed or not
     */
    public function isUserExisted($email) {
        $stmt = $this->conn->prepare("SELECT email from users WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt->close();
            return true;
        } else {
            // user not existed
            $stmt->close();
            return false;
        }
    }

    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {

        $hash = base64_encode(sha1($password . $salt, true) . $salt);

        return $hash;
    }
	
	public function getUsers()
	{

		$itemarray = [];

		$sqlquery = "SELECT * from users";
            

		$result = $this->conn->query($sqlquery) or die($this->conn->error);
		$i = 0;
		while($resultrow = mysqli_fetch_array($result))
		{
			$itemarray[$i]['id'] = $resultrow['id'];
			$itemarray[$i]['username'] = $resultrow['username'];
			$i++;
		}
		return $itemarray;

		
	}

}

?>
