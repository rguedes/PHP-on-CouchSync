Introduction
============

[PHP On Couch](http://github.com/dready92/PHP-on-Couch/) tries to provide an easy way to work with your [CouchDB](http://couchdb.apache.org) [documents](http://wiki.apache.org/couchdb/HTTP_Document_API) with [PHP](http://php.net). 

[PHP On CouchSync](https://github.com/mryellow/PHP-on-CouchSync) refactors this work for CouchDB based Sync Gateway for Couchbase Lite mobile sync.





    <?PHP
	use CouchSync\Client as SyncClient;
	use CouchSync\Admin as SyncAdmin;

	echo "\nAdmin Connection\n";
	$admclient = new SyncClient("http://localhost:4985/", "sync_gateway" );

	echo "\nCompact database\n";
	try {
		$res = $admclient->compactDatabase();
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to compact database: ".$e->getMessage());
	}

	echo "Admin class\n";
	$adm = new SyncAdmin($admclient);

	echo "\nCreate user\n";
	try {
		$res = $adm->createUser("joe@email.com","secret");
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to create user: ".$e->getMessage());
	}

	echo "\nCreate role\n";
	try {
		$res = $adm->createRole("testrole",array('testchannel'));
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to create role: ".$e->getMessage());
	}

	echo "\nCreate session for user\n";
	try {
		$session = $adm->createSession('joe@email.com',30);
		var_dump(array('res'=>$session));
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

	echo "\nCreate 3 docs\n";
	try {
		$doc1 = new \stdClass();
		$doc1->_id = 'testdoc1';
		$doc1->name = $doc1->_id;
		$doc1->field = 'testfield';
		$doc1->channels = array('testchannel');
		$doc2 = clone($doc1);
		$doc2->_id = 'testdoc2';
		$doc2->name = $doc2->_id;
		$doc3 = clone($doc1);
		$doc3->_id = 'testdoc3';
		$doc3->name = $doc3->_id;
		
		$docs = array($doc1,$doc2,$doc3);
		$res = $client->storeDocs($docs);
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to create docs: ".$e->getMessage());
	}

	echo "\Get all docs\n";
	try {
		$alldocs = $client->getAllDocs();
		var_dump(array('res'=>$alldocs));
	} catch ( Exception $e ) {
		die("unable to get all docs: ".$e->getMessage());
	}

	echo "\nUpdate doc\n";
	try {
		$doc = $client->getDoc('testdoc');
		var_dump(array('res'=>$res));
		$doc->field = 'testfield_updated';
		$res = $client->storeDoc($doc);
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to update doc: ".$e->getMessage());
	}




	echo "\nDelete doc\n";
	try {
		$doc = $client->getDoc('testdoc');
		$res = $client->deleteDoc($doc);
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to delete doc: ".$e->getMessage());
	}

	echo "\nDelete docs\n";
	try {
		$docs = array(
			$client->getDoc('testdoc1'),
			$client->getDoc('testdoc2'),
			$client->getDoc('testdoc3')
		);
		$res = $client->deleteDocs($docs);
		var_dump(array('res'=>$res));
	} catch ( Exception $e ) {
		die("unable to delete docs: ".$e->getMessage());
	}

	echo "\nGet _changes feed\n";
	try {
		// hmm maybe easier to just hit "feed" instead of "getChanges"
		//$res = $client->getChanges(); 
		$res = $client->feed('normal'); // normal|longpoll|continuous|websocket
		var_dump(array('res'=>$res));
		unset($users);
	} catch ( Exception $e ) {
		die("unable to get changes feed: ".$e->getMessage());
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


        
Feedback
========

Don't hesitate to submit feedback, bugs and feature requests!

Resources
=========

[Admin REST API](http://docs.couchbase.com/sync-gateway/#admin-rest-api)

[Couchbase Lite REST API](http://developer.couchbase.com/mobile/develop/references/couchbase-lite/rest-api/index.html)

[Sync Gateway - Authorizing Users](http://developer.couchbase.com/mobile/develop/guides/sync-gateway/administering-sync-gateway/authorizing-users/index.html)

[Sync Gateway - Routing handlers](https://github.com/couchbase/sync_gateway/blob/master/src/github.com/couchbaselabs/sync_gateway/rest/routing.go#L91)
