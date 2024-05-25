<?php

namespace App\Model;

enum TaskState: int
{
    case PENDING = 1;
    case IN_PROGRESS = 2;
    case DONE = 3;
}
