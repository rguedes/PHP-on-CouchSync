<?PHP

    /*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

	Modified by Mr-Yellow for Sync Gateway REST APIs.
*/
namespace CouchSync;


/**
* Special class to handle administration tasks
* - create administrators
* - create users
* - create roles
* - assign roles to users
*
*
*
*/
class Admin {
	/**
	* @var reference to our CouchDB client
	*/
	private $client = null;


	/**
	* @var the name of the CouchDB server "users" endpoint
	*/
	private $usersdb = "_user";

	/**
	* @var the name of the CouchDB server "session" endpoint
	*/
	private $sessionsdb = "_session";

	/**
	* @var the name of the CouchDB server "session" endpoint
	*/
	private $rolesdb = "_role";

	/**
	*constructor
	*
	* @param Client $client the Client instance
	* @param array $options array. For now the only option is "users_database" to override the defaults "_users"
	*/
	public function __construct ( Client $client, $options = array() ) {
		$this->client = $client;
		if ( is_array($options) && isset($options["users_database"]) ) {
			$this->usersdb = $options["users_database"];
		}
	}

	/**
	*Set the name of the users database (_users by default)
	*
	*@param string $name CouchDB users database name (_users is the default)
	*/
	public function setUsersDatabase ($name) {
		$this->usersdb = $name;
	}

	/**
	*get the name of the users database this class will use
	*
	*
	*@return string users database name
	*/
	public function getUsersDatabase () {
		return $this->usersdb;
	}

	private function build_url ($parts) {
		$back = $parts["scheme"]."://";
		if ( !empty($parts["user"]) ) {
			$back.=$parts["user"];
			if ( !empty($parts["pass"]) ) {
				$back.=":".$parts["pass"];
			}
			$back.="@";
		}
		$back.=$parts["host"];
		if ( !empty($parts["port"]) ) {
			$back.=":".$parts["port"];
		}
		$back.="/";
		if ( !empty($parts["path"]) ) {
			$back.=$parts["path"];
		}
		return $back;
	}

	/**
	* create a session cookie
	*
	* @param string $login user login
	* @param int $ttl time-to-live in seconds
	* @return stdClass Session response
	* @throws InvalidArgumentException
	*/
	public function createSession ($login,$ttl=1200) {
		if ( strlen($login) < 1 ) {
			throw new \InvalidArgumentException("Login can't be empty");
		}
		$user = new \stdClass();
		$user->name = $login;
		$user->ttl = intval($ttl);

		return $this->client->storeDoc($user, $this->sessionsdb);
	}

	/**
	* create a user
	*
	* @param string $login user login
	* @param string $password user password
	* @param string $email user email address
	* @param array $roles add additionnal roles to the new user
	* @return stdClass CouchDB user creation response (the same as a document storage response)
	* @throws InvalidArgumentException
	*/
	public function createUser ($login, $password, $roles = array(), $channels = array() ) {
		$password = (string)$password;
		if ( strlen($login) < 1 ) {
			throw new \InvalidArgumentException("Login can't be empty");
		}
		if ( strlen($password) < 1 ) {
			throw new \InvalidArgumentException("Password can't be empty");
		}
		$user = new \stdClass();
		$user->admin_channels = $channels;
		$user->admin_roles = $roles;
		//$user->all_channels = null;  // This is a derived property and changes to it are ignored.
		//$user->disabled = null; // This property is usually not included. if the value is set to true, access for the account is disabled.
		$user->email = $login;
		$user->name = $login;
		$user->password = $password;
		//$user->roles = null; // This is a derived property and changes to it are ignored.

		$user->_id = $login; // Turns on PUT
		return $this->client->storeDoc($user, $this->usersdb);
	}


	/**
	* Permanently removes a CouchDB User
	*
	*
	* @param string $login user login
	* @return stdClass CouchDB server response
	* @throws InvalidArgumentException
	*/
	public function deleteUser ( $login ) {
		if ( strlen($login) < 1 ) {
			throw new \InvalidArgumentException("Login can't be empty");
		}
		$this->client->asDocuments();
		$doc = $this->client->getDoc($login, $this->usersdb);
		return $this->client->deleteDoc($doc, $this->usersdb);
	}

	/**
	* returns the document of a user
	*
	* @param string $login login of the user to fetch
	* @return stdClass CouchDB document
	* @throws InvalidArgumentException
	*/
	public function getUser ($login) {
		if ( strlen($login) < 1 ) {
			throw new \InvalidArgumentException("Login can't be empty");
		}
		return $this->client->getDoc($login, $this->usersdb);
	}

	/**
	* returns all users
	*
	* @param boolean $include_docs if set to true, users documents will also be included
	* @return array users array : each row is a stdObject with "id", "rev" and optionally "doc" properties
	*/
	public function getAllUsers($include_docs = false) {
		$doc = $this->client->getDoc('', $this->usersdb);
		if ( $include_docs ) {
			$users = array();
			foreach ($doc as $login) {
				$this->client->asDocuments();
				$res = $this->client->getDoc($login, $this->usersdb);
				if ( $res instanceof Document ) {
					$users[$login] = $res->getFields();
				} else {
					$users[$login] = $res;
				}
			}
			return $users;
		}
		return $doc;
	}

	/**
	* create a role
	*
	* @param string $rolename role name
	* @return stdClass CouchDB role creation response (the same as a document storage response)
	* @throws InvalidArgumentException
	*/
	public function createRole ($rolename, $channels = array()) {
		if ( strlen($rolename) < 1 ) {
			throw new \InvalidArgumentException("Role name can't be empty");
		}
		$role = new \stdClass();
		$role->name = $rolename;
		$role->admin_channels = $channels;
		$role->_id = $rolename; // Turns on PUT
		return $this->client->storeDoc($role, $this->rolesdb);
	}

	/**
	* Permanently removes a CouchDB Role
	*
	*
	* @param string $role role name
	* @return stdClass CouchDB server response
	* @throws InvalidArgumentException
	*/
	public function deleteRole ( $role ) {
		if ( strlen($role) < 1 ) {
			throw new \InvalidArgumentException("Role name can't be empty");
		}
		$this->client->asDocuments();
		$doc = $this->client->getDoc($role, $this->rolesdb);
		return $this->client->deleteDoc($doc, $this->rolesdb);
	}

	/**
	* Add a role to a user document
	*
	* @param string|stdClass $login the user login (as a string) or the user document ( fetched by getUser() method )
	* @param string $role the role to add in the list of roles the user belongs to
	* @return boolean true if the user $user now belongs to the role $role
	* @throws InvalidArgumentException
	*/
	public function addRoleToUser ($login,$role) {
		if ( is_string($login) ) {
			$user = $this->getUser($login);
			if ( !isset($user->admin_roles) ) $user->admin_roles = array();
		} elseif ( !property_exists($user,"_id") || !property_exists($user,"admin_roles") ) {
			throw new \InvalidArgumentException("user parameter should be the login or a user document");
		}
		if ( !in_array($role,$user->admin_roles) ) {
			$user->admin_roles[] = $role;
			$user->_id = $login; // Turns on PUT
			$client = clone($this->client);
			$client->storeDoc($user, $this->usersdb);
		}
		return true;
	}

	/**
	* Remove a role from a user document
	*
	* @param string|stdClass $login the user login (as a string) or the user document ( fetched by getUser() method )
	* @param string $role the role to remove from the list of roles the user belongs to
	* @return boolean true if the user $user don't belong to the role $role anymore
	* @throws InvalidArgumentException
	*/
	public function removeRoleFromUser ($login,$role) {
		if ( is_string($login) ) {
			$user = $this->getUser($login);
		} elseif ( !property_exists($user,"_id") || !property_exists($user,"admin_roles") ) {
			throw new \InvalidArgumentException("user parameter should be the login or a user document");
		}
		if ( in_array($role,$user->admin_roles) ) {
			$user->admin_roles = $this->rmFromArray($role, $user->admin_roles);
			$user->_id = $login; // Turns on PUT
			$client = clone($this->client);
			$client->storeDoc($user, $this->usersdb);
		}
		return true;
	}

	/**
	* Add a channel to a user document
	*
	* @param string|stdClass $login the user login (as a string) or the user document ( fetched by getUser() method )
	* @param string $channel the channel to add in the list of channels the user belongs to
	* @return boolean true if the user $user now belongs to the channel $channel
	* @throws InvalidArgumentException
	*/
	public function addChannelToUser ($login,$channel) {
		if ( is_string($login) ) {
			$user = $this->getUser($login);
			if ( !isset($user->admin_channels) ) $user->admin_channels = array();
		} elseif ( !property_exists($user,"_id") || !property_exists($user,"admin_channels") ) {
			throw new \InvalidArgumentException("user parameter should be the login or a user document");
		}
		if ( !in_array($channel,$user->admin_channels) ) {
			$user->admin_channels[] = $channel;
			$user->_id = $login; // Turns on PUT
			$client = clone($this->client);
			$client->storeDoc($user, $this->usersdb);
		}
		return true;
	}

	/**
	* Remove a channel from a user document
	*
	* @param string|stdClass $login the user login (as a string) or the user document ( fetched by getUser() method )
	* @param string $channel the channel to remove from the list of channels the user belongs to
	* @return boolean true if the user $user don't belong to the channel $channel anymore
	* @throws InvalidArgumentException
	*/
	public function removeChannelFromUser ($login,$channel) {
		if ( is_string($login) ) {
			$user = $this->getUser($login);
		} elseif ( !property_exists($user,"_id") || !property_exists($user,"admin_channels") ) {
			throw new \InvalidArgumentException("user parameter should be the login or a user document");
		}
		if ( in_array($channel,$user->admin_channels) ) {
			$user->admin_channels = $this->rmFromArray($channel, $user->admin_channels);
			$user->_id = $login; // Turns on PUT
			$client = clone($this->client);
			$client->storeDoc($user, $this->usersdb);
		}
		return true;
	}

/// /roles

	private function rmFromArray($needle, $haystack) {
		$back = array();
		foreach ( $haystack as $one ) {
			if ( $one != $needle ) { $back[] = $one; }
		}
		return $back;
	}

}
