Introduction
============

[PHP On Couch](http://github.com/dready92/PHP-on-Couch/) tries to provide an easy way to work with your [CouchDB](http://couchdb.apache.org) [documents](http://wiki.apache.org/couchdb/HTTP_Document_API) with [PHP](http://php.net). 

[PHP On CouchSync](https://github.com/mryellow/PHP-on-CouchSync) refactors this work for CouchDB based Sync Gateway for Couchbase Lite mobile sync.





    <?PHP
    use CouchSync\Client as SyncClient;
	use CouchSync\Admin as SyncAdmin;

	echo "Connection\n";
	$admclient = new SyncClient("http://localhost:4985/", "sync_gateway" );
	echo "Admin functions\n";
	$adm = new SyncAdmin($admclient);
	echo "Create user\n";
	try {
		$adm->createUser("joe@email.com","secret");
	} catch ( Exception $e ) {
		die("unable to create user: ".$e->getMessage());
	}

	echo "Get All users\n";
	try {
		$users = $adm->getAllUsers(true);
		var_dump(array('joe@email.com'=>$users['joe@email.com']->getFields()));
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
        
Feedback
========

Don't hesitate to submit feedback, bugs and feature requests!

Resources
=========

[Admin REST API](http://docs.couchbase.com/sync-gateway/#admin-rest-api)

[Sync REST API](http://developer.couchbase.com/mobile/develop/references/couchbase-lite/rest-api/index.html)

[Sync Gateway - Authorizing Users](http://developer.couchbase.com/mobile/develop/guides/sync-gateway/administering-sync-gateway/authorizing-users/index.html)

[Sync Gateway - Routing handlers](https://github.com/couchbase/sync_gateway/blob/master/src/github.com/couchbaselabs/sync_gateway/rest/routing.go#L91)
