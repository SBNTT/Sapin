<?php

namespace App\Controller;

use App\Component\CreateTaskPage;
use Sapin\Sapin;

Sapin::compileAndRender(CreateTaskPage::class, fn() => new CreateTaskPage());
