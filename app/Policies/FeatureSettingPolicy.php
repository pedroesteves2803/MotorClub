<?php

namespace App\Policies;

use App\Models\FeatureSetting;
use App\Models\User;

class FeatureSettingPolicy
{
    public function before(User $user, string $ability): bool
    {
        return $user->isSuperAdmin();
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, FeatureSetting $record): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, FeatureSetting $record): bool
    {
        return false;
    }

    public function delete(User $user, FeatureSetting $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
