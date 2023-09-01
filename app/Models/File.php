<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;

    /**
     * The database table associated with the model.
     */
    protected $table = 'files';

    /**
     * The attributes that are mass assignable.
     *
     * These attribute names can be filled using the Eloquent create and update methods.
     *
     * @var array<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'path',
        'size',
        'type',
    ];
}
