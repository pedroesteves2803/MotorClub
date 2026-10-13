<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->vehicle->user_id;
    }

    public function view(?User $user, Project $project): bool
    {
        return $project->publiclyVisible() || ($user && $this->update($user, $project));
    }
}
