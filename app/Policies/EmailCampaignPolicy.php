<?php

namespace App\Policies;

use App\Models\EmailCampaign;
use App\Models\User;

class EmailCampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, EmailCampaign $campaign): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, EmailCampaign $campaign): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, EmailCampaign $campaign): bool
    {
        return $user->role === 'admin';
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function restore(User $user, EmailCampaign $campaign): bool
    {
        return $user->role === 'admin';
    }

    public function restoreAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, EmailCampaign $campaign): bool
    {
        return $user->role === 'admin';
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->role === 'admin';
    }
}
