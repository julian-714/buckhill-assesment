<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JwtToken extends Model
{
    use HasFactory;

    /**
     * The database table associated with the model.
     */
    protected $table = 'jwt_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * These attribute names can be filled using the Eloquent create and update methods.
     *
     * @var array<string>
     */
    protected $casts = [
        'restrictions' => 'array',
        'permissions' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * These attribute names can be filled using the Eloquent create and update methods.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'unique_id',
        'token_title',
        'restrictions',
        'permissions',
        'expires_at',
        'last_used_at',
        'refreshed_at',
    ];
}
