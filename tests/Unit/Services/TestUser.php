<?php

namespace Tests\Unit\Services;

use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Database\Factories\UserFactory;

class TestUser extends User
{
    protected $guard_name = 'web';
    
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new()->setModel(static::class);
    }
}
