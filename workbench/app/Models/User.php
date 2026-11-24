<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Javaabu\Mediapicker\Concerns\OwnsMedia;
use Javaabu\Mediapicker\Contracts\MediaOwner;
use Javaabu\Permissions\Traits\HasRoles;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements MediaOwner
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use InteractsWithMedia;
    use OwnsMedia;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function registerMediaCollections(): void
    {
        $this->registerMediapickerCollections();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerMediapickerConversions($media);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
