<?php

namespace App\Policies;

use App\Models\EmailCampaignTemplate;
use App\Models\User;

class EmailCampaignTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, EmailCampaignTemplate $template): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, EmailCampaignTemplate $template): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, EmailCampaignTemplate $template): bool
    {
        return $user->role === 'admin';
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function restore(User $user, EmailCampaignTemplate $template): bool
    {
        return $user->role === 'admin';
    }

    public function restoreAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, EmailCampaignTemplate $template): bool
    {
        return $user->role === 'admin';
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->role === 'admin';
    }
}
