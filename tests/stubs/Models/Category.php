<?php

namespace App\Models;

use Javaabu\Helpers\AdminModel\AdminModel;
use Javaabu\Helpers\AdminModel\IsAdminModel;
use Javaabu\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model implements AdminModel
{
    use HasFactory;
    use IsAdminModel;
    use LogsActivity;

    /**
     * The attributes that would be logged
     *
     * @var array
     */
    protected static array $logAttributes = ['*'];

    /**
     * Changes to these attributes only will not trigger a log
     *
     * @var array
     */
    protected static array $ignoreChangedAttributes = ['created_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * The attributes that are cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name',
        'slug',
    ];

    /**
     * Get the admin url attribute
     */
    public function getAdminUrlAttribute(): string
    {
        return route('admin.categories.show', $this);
    }
}
