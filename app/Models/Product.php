<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The database table associated with the model.
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * These attribute names can be filled using the Eloquent create and update methods.
     *
     * @var array<string>
     */
    protected $fillable = ['category_uuid', 'uuid', 'title', 'price', 'description', 'metadata'];

    public static function getPrice($uuid)
    {
        return self::where('uuid', $uuid)->first();
    }
}
