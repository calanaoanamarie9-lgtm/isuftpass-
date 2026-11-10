<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes
|--------------------------------------------------------------------------
|
| The post-submission rating modal that used the one route here is gone: a
| request or a booking is not rated when it is filed, only once the service
| is finished - the document claimed, the visit completed - from the page
| that shows the finished record. The group (and the web session stack
| registered for it in bootstrap/app.php) is kept so a future same-origin
| endpoint has somewhere to live.
|
*/
