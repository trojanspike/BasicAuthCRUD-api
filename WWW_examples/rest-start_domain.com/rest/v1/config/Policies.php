<?php

/* By default all api's will need to go through Auth.php */
$policies = [];

/*
$policies['example'] = false // no Auth required
$policies['example'] = true // Auth required (default)

// noAuth depending on request type
$policies['example'] = ['GET', 'PUT'] // GET & PUT go through NoAuth.php
*/
$policies['exampleNoAuth'] = ['GET', 'POST', 'PUT', 'DELETE'];
$policies['exampleAuth'] = ['GET'];

return $policies;

?>