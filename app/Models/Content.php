<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'content_uuid',
        'title',
        'slug',
        'description',
        'content_type',
        'release_year',
        'language',
        'genres',
        'poster_url',
        'imdb_rating',
        'search_text',
        'status',
    ];

    protected $casts = [
        'genres' => 'array',
        'release_year' => 'integer',
        'imdb_rating' => 'float',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(
            CastModel::class,
            'content_cast',
            'content_id',
            'cast_id'
        );
    }
}