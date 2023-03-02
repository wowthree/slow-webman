<?php

namespace app\model;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AdminUser extends BaseModel
{
    protected $guarded = [];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format($this->getDateFormat());
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_users', 'user_id', 'role_id')->withTimestamps();
    }

    public function avatar(): Attribute
    {
        return new Attribute(fn ($value) => $value ? admin_resource_full_path($value) : url(config('admin.default_avatar')));
    }

    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (AdminUser $model) {
            $model->roles()->detach();
        });
    }

    public function allPermissions(): Collection
    {
        return $this->roles()->with('permissions')->get()->pluck('permissions')->flatten();
    }


    public function can($abilities, $arguments = []): bool
    {
        if (empty($abilities)) {
            return true;
        }

        if ($this->isAdministrator()) {
            return true;
        }

        return $this->roles->pluck('permissions')->flatten()->pluck('slug')->contains($abilities);
    }

    public function isAdministrator(): bool
    {
        return $this->isRole('administrator');
    }

    public function isRole(string $role): bool
    {
        return $this->roles->pluck('slug')->contains($role);
    }

    public function inRoles(array $roles = []): bool
    {
        return $this->roles->pluck('slug')->intersect($roles)->isNotEmpty();
    }

    public function visible(array $roles = []): bool
    {

        if ($this->isAdministrator()) {
            return true;
        }
        if (empty($roles)) {
            return false;
        }
        $roles = array_column($roles, 'slug');

        return $this->inRoles($roles);
    }
}
