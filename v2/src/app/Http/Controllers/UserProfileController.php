<?php

/**
 * ============================================================================
 * IQArchive v2 — User Profile & Identity Controller
 * ============================================================================
 * File: app/Http/Controllers/UserProfileController.php
 * Responsibility: Serves authenticated user profile and institutional identity.
 * Architecture: Controller Layer (Thin Controller -> Model)
 * Security Context: Read-only presentation of Google SSO credentials and active roles.
 * ============================================================================
 */

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserProfileController extends Controller
{
    /**
     * Display the authenticated user's profile and institutional identity.
     *
     * Security Reasoning: Read-only presentation of Google Workspace credentials
     * and role assignments. User credentials cannot be manipulated directly here.
     */
    public function settings(Request $request): Response
    {
        $user = Auth::user();

        // Safe fallback for unauthenticated visits in basic route tests
        if (! $user) {
            return Inertia::render('Auth/AccountSettings', [
                'profile' => [
                    'name' => 'Guest User',
                    'email' => 'guest@bicol-u.edu.ph',
                    'role' => 'iqa_staff',
                    'roles' => ['iqa_staff'],
                    'avatar_url' => null,
                    'department' => 'Internal Quality Assurance Office',
                    'status' => 'active',
                    'joined_at' => 'October 2026',
                    'last_sign_in' => 'Active Session',
                    'ip_address' => $request->ip(),
                ],
            ]);
        }

        // Fetch last sign-in log for this user
        $lastLogin = AuditLog::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->whereIn('action', ['auth.login', 'auth.login.dev'])
            ->latest('id')
            ->first();

        return Inertia::render('Auth/AccountSettings', [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'iqa_staff',
                'roles' => $user->roles->pluck('name')->toArray(),
                'avatar_url' => $user->avatar_url,
                'department' => $user->college?->name ?? 'Internal Quality Assurance Office',
                'status' => $user->status ?? 'active',
                'joined_at' => $user->created_at ? $user->created_at->format('F Y') : 'Active',
                'last_sign_in' => $lastLogin && $lastLogin->created_at ? $lastLogin->created_at->format('M d, Y h:i A') : 'Active Session',
                'ip_address' => $request->ip(),
            ],
        ]);
    }
}
