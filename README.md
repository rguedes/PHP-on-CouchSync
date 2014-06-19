Introduction
============

[PHP On Couch](http://github.com/dready92/PHP-on-Couch/) tries to provide an easy way to work with your [CouchDB](http://couchdb.apache.org) [documents](http://wiki.apache.org/couchdb/HTTP_Document_API) with [PHP](http://php.net). 

[PHP On CouchSync](https://github.com/mryellow/PHP-on-CouchSync) refactors this work for CouchDB based Sync Gateway for Couchbase Lite mobile sync.





    <?PHP
	use CouchSync\Client as SyncClient;
	use CouchSync\Admin as SyncAdmin;

	echo "\nAdmin Connection\n";
	$admclient = new SyncClient("http://localhost:4985/", "sync_gateway" );
	echo "Admin class\n";
	$adm = new SyncAdmin($admclient);

	echo "\nCreate user\n";
	try {
		$res = $adm->createUser("joe@email.com","secret");
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to create user: ".$e->getMessage());
	}

	echo "\nAdd channel to user\n";
	try {
		$res = $adm->addChannelToUser('joe@email.com','testchannel');
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to add channel to user: ".$e->getMessage());
	}

	echo "\nClient Connection\n";
	$client = new SyncClient("http://joe%40email.com:secret@localhost:4984/", "sync_gateway" );

	echo "\nCreate doc\n";
	try {
		$doc = new \stdClass();
		$doc->_id = 'testdoc';
		$doc->name = 'testdoc';
		$doc->field = 'testfield';
		$doc->channels = array('testchannel');
		$res = $client->storeDoc($doc);
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to create doc: ".$e->getMessage());
	}

	echo "\nUpdate doc\n";
	try {
		$doc = $client->getDoc('testdoc');
		var_dump(array('res'=>$res));
		$doc->field = 'testfield_updated';
		$res = $client->storeDoc($doc);
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to create doc: ".$e->getMessage());
	}

	echo "\nDelete doc\n";
	try {
		$doc = $client->getDoc('testdoc');
		$res = $client->deleteDoc($doc);
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to create doc: ".$e->getMessage());
	}

	/*
	// Endpoint on Sync API not going to work for this.
	// https://github.com/couchbase/sync_gateway/issues/336
	echo "\nQuery view\n";
	try {
		$res = $client->getView('test','test');
		var_dump(array('res'=>$res));
		
		$res = $client->getView('sync_gateway','channels');
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to get view: ".$e->getMessage());
	}
	*/

	echo "\nCreate role\n";
	try {
		$res = $adm->createRole("testrole",array('testchannel'));
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to create role: ".$e->getMessage());
	}

	echo "\nCreate session for user\n";
	try {
		$res = $adm->createSession('joe@email.com',30);
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to create session: ".$e->getMessage());
	}

	echo "\nAdd role to user\n";
	try {
		$res = $adm->addRoleToUser('joe@email.com','testrole');
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to add role to user: ".$e->getMessage());
	}
	echo "\nAdd channel to user\n";
	try {
		$res = $adm->addChannelToUser('joe@email.com','testchannel');
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to add channel to user: ".$e->getMessage());
	}

	echo "\nGet user 'joe@email.com'\n";
	try {
		$res = $adm->getUser('joe@email.com');
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to get user: ".$e->getMessage());
	}

	echo "\nRemove role from user\n";
	try {
		$res = $adm->removeRoleFromUser('joe@email.com','testrole');
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to remove role from user: ".$e->getMessage());
	}

	echo "\nRemove channel from user\n";
	try {
		$res = $adm->removeChannelFromUser('joe@email.com','testchannel');
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to remove channel from user: ".$e->getMessage());
	}

	echo "\nGet All users\n";
	try {
		$users = $adm->getAllUsers(true);
		var_dump(array('joe@email.com'=>$users['joe@email.com']));
		unset($users);
	} catch ( Exception $e ) {
		die("unable to get all users: ".$e->getMessage());
	}

	echo "Delete user\n";
	try {
		$adm->deleteUser("joe@email.com");
	} catch ( Exception $e ) {
		die("unable to delete user: ".$e->getMessage());
	}

	echo "Delete role\n";
	try {
		$adm->deleteRole("testrole");
	} catch ( Exception $e ) {
		die("unable to delete role: ".$e->getMessage());
	}


        
Feedback
========

Don't hesitate to submit feedback, bugs and feature requests!

Resources
=========

[Admin REST API](http://docs.couchbase.com/sync-gateway/#admin-rest-api)

[Couchbase Lite REST API](http://developer.couchbase.com/mobile/develop/references/couchbase-lite/rest-api/index.html)

[Sync Gateway - Authorizing Users](http://developer.couchbase.com/mobile/develop/guides/sync-gateway/administering-sync-gateway/authorizing-users/index.html)

[Sync Gateway - Routing handlers](https://github.com/couchbase/sync_gateway/blob/master/src/github.com/couchbaselabs/sync_gateway/rest/routing.go#L91)
