<?php

namespace App\Controller;

use App\Component\ErrorPage;
use Sapin\Sapin;

http_response_code(404);
Sapin::render(new ErrorPage(
    message: 'Error 404: Not Found'
));
