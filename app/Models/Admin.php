<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'status'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    use Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * SaaS admins only manage the platform-level modules: tenants and plans.
     *
     * @return array<int, string>
     */
    public function allowedModules(): array
    {
        return ['tenants', 'plans'];
    }

    public function canAccessModule(string $module): bool
    {
        return in_array($module, $this->allowedModules(), true);
    }

    public function canDo(string $module, string $action): bool
    {
        return $this->canAccessModule($module);
    }
}
