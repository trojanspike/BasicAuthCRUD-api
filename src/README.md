#### Basic Auth CRUD api
##### no composer - just require
- Version 0.1.37
- Flexable usage , some small examples below & example folder
- Feedback and improvements welcome 
- [Try a small demo at crud-api.uk.to](http://crud-api.uk.to " Basic crud - rest API  ")
- View # /WWW_examples/crud_api.uk.to/ Code
- Login from [remote access](http://crud-api-remote.uk.to " Basic crud - rest API #remote ") with Javascript to hit the API
```php
<?php
// index.php
require_once __DIR__.'/src/Api.php';
require_once __DIR__.'/src/Rest.php'; /* optional | you can write your own */
/*
Rest policies keep access restricted
*/
$path = preg_replace('/^\//', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

/* /show page  */
if( $path == '' ){
    header('Location:/home');
}

/* How strict to make the REQUEST_URI , here I just was chars int & / */
if( preg_match("/^([\/a-z0-9]+)$/", $path) && preg_match("/^home$/", $path) == false ){
	/* Let the rest lib know where the config folder is */
    Rest::$Dir = __DIR__.'/../rest/';
    /* Pass through the URI parts aray */
    Rest::init(explode('/', $path));
}

?>
```

```javascript
/* jQuery implementation */
$.ajax({
	type : 'GET',
    beforeSend: function(xhr) {
		xhr.setRequestHeader("Accept", 'application/json');
		xhr.setRequestHeader("X-username", 'user123');
		xhr.setRequestHeader("X-password", 'pass123');
    },
    dataType: "json",
    url: 'http://crud-api.uk.to/v1/user',
    success: function(data) {
        console.log(data);
    }
});
/* angular implementation */
$http({method: 'GET', 
			url: 'http://crud-api.uk.to/v1/user', 
			headers: {
				'Accept': 'application/json',
				'X-username' : 'user123',
				'X-password' : 'pass123'}
		}).then(function(obj){
			console.log(obj);
		});
```

### Requirements

* PHP 5.4 >

#### Api {static class} methods
* ::HTTP_VERBS{ get, post, put, delete }
* ::error($1) # $1 = function($1, $2) : $1 = error message , $2 = Response class
* ::inject($1, $2) = $1{string}, $2{*any , object , function , class etc} # injects last into ::auth , ::VERB, get, put etc
* ::auth($1) # $1 = function($1, $2, $3, $4) : $1 = Request class , $2 = Response class , $3 = run, $4 = $injects
* ::$uri (String) , $path 
* Example might be :
```php
	Api::inject('run', true);
	Api::post(function($req, $res, $injects){
		$res->json( $req->input() );
	});
	Api::error(function($mess, $res){
		$res->status($mess['status'])json( $mess );
	});
	Api::auth(function($req, $res, $run, $injects){
		if($injects['run']){
			$run();
		} else {
			$res->forBidden();
		}
	});
```
#### Rest {static class} methods
* ::$Dir (String) Path to the rest config folder : ( /api/*APIS , /config/{Auth,NoAuth,Injects,Policies}.php )
* ::$debug (Boolean) output errors ?
* ::init($1) # $1 array of the REQUEST_URI , exploded
```php
	Rest::$Dir = realpath(__DIR__.'/Rest/');
	Rest::$debug = true;
	Rest::init( explode('/', $REQUEST_URI) );
```

#### Request {object class} methods
* ->verb	# current http verb , X-verb over ride this & can be overridden
* ->accept # The accept header sent from user
* ->basicAuth($1) // $1 { string#username / password | array | empty } returns value or if array return Values else false if all not found or Full-Array if empty $_AUTH_ARRAY
* ->header($1) // $1 { string | array | empty } returns value if found else false or if array return Values else false if all not found # or Full-Array if empty $_HEADERS
* ->input($1) // $1 { string | array | empty } returns value if found else false or if array return Values else false if all not found # or Aull-Array if empty $_POST / $_PUT etc
* ->get($1) // $1 { string | array | empty } returns value if found else false or if array return Values else false if all not found # or Full-Array if empty $_GET array
* ->params($1) // $1 {int | empty} return array of params when avail else return false , return full array of params when empty
* ->uri // The uri { String }
* Example might be :
```php
	Api::put(function($request, $response){
		$params = $request->params(); // all params
		$both = $request->params(2); // /param1/param2
		$id = $request->get('post-id'); // $_GET['post-id'];
		$inputs = $request->input(); // like $_POST only PUT
		$res->setContent('image/jpg')->outPut( file_get_content( $request->uri ) ); // uri = /userid/img/avatar.jpp
	});
```
#### Response {object class} methods
* ->status($1)  // $1 { int } set HTTP status code. Can be chained
* ->setContent($1) // $1 { String } , set the content-type, Can be chained
* ->setHeader($1) // $1 { String | array }, set header
* ->outPut($1) // $1 { String }, out puts content

Helper shortcuts
* ->json($1)  // $1 { array | object } out json encoded, headers set to application/json
* ->badRequest() // sets status 400 , output jsonObject {message:'ClientError'}
* ->notFound() // sets status 404 , output jsonObject {message:'ClientError'}
* ->unAuth() // sets status 401 , output jsonObject {message:'ClientError'}
* ->ok() // sets status 200 , output jsonObject {message:'Success'}
* ->created() // sets status 201 , output jsonObject {message:'Success'}
* Example might be :
```php
	Api::post(function($request, $response){
		$message = $request->input('message');
		DB->insert($message);
		$response->created();
	});
	Api::get(function($request , $response){
		$response->json( DB->getAll(true) );
	});
	Api::error(function($request, $response){
		$response->status(500)->setContent('text/plain')->outPut('Error');
	});
```
#### Allow - Origin & Header
- Allow-Origin: *
- Allow-Headers : Authorization, Content-Type, Accept, X-username , X-password , X-verb , Auth-Token

```bash
	curl http://domain.com/api?module=users -X POST -d '{"key":"val"}' -H 'accept:application/json' # open api
	curl -u user:pass http://domain.com/auth?module=users -X POST -d '{"key":"val"}' -H 'accept:application/json' # basicAuth api
	curl http://domain.com/headerAuth?module=users -X POST -d '{"key":"val"}' -H 'X-username:user' -H 'X-password:pass' -H 'accept:application/json' # header auth
	## using injects
	curl -u user:pass http://domain.com/auth?module=injects -X POST -d '{"key":"val"}' -H 'accept:application/json' # basicAuth /api/inject
	curl -u user:pass http://domain.com/auth?module=injects -X PUT -d '{"job":"Security"}' -H 'accept:application/json' # basicAuth /api/inject
	### Auth using Auth-Token
	curl http://domain.com/token/ -X GET -H 'Auth-Token:tk-1fg5@e45s' -H 'accept:application/json'
	curl http://domain.com/token/ -X PUT -H 'Auth-Token:tk-1fg5@e45s' -H 'accept:application/json'
```

```bash
 ## Rest api
 # open to web access - no header accept needed
 curl https://domain.com/api/web/open/15/tester/value
 # closed to web , header accept is needed
 curl https://domain.com/api/web/closed/15/tester/value -H 'accept:application/json'
 
 # auth require , Token / HeaderAuth / BasicAuth
 curl https://domain.com/api/users/tokenAuth/15/tester/value -H 'Auth-Token:abc123' -H 'accept:application/json'
 curl https://domain.com/api/users/headerAuth/15/tester/value -H 'x-username:user123' -H 'x-password:pass123' -H 'accept:application/json'
 curl -u user123:pass123 https://domain.com/api/users/basicAuth/15/tester/value -H 'accept:application/json'
```

```bash
 # Image Upload
 curl -F "image_key=@./image.jpg" http://crud-api.uk.to/v1/upload -i -H 'authToken:abc132'
 
```


TODO
* ~~add headers X-* when func "getallheaders" isn't avail~~
* ~~Request #get,input,header to accept array and returns all if avail else false. ->get(['id','key','page'])~~
* ~~add Api::inject method to add onto Api::VERB - callback i.e Api::inject([DB, $Data_arrays])~~
* ~~Improve cors & header access~~
