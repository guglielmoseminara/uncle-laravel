<?php

namespace UncleProject\UncleLaravel\Classes;

use Illuminate\Database\Eloquent\Model;
use UncleProject\UncleLaravel\Traits\HasUncleXML;
use App;

class BaseModel extends Model {
    use HasUncleXML;
}