<?php

/**
 * ============================================================================
 * IQArchive v2 — User Profile & Identity Feature Test
 * ============================================================================
 * File: tests/Feature/UserProfileSettingsTest.php
 * Responsibility: Verifies user profile rendering, institutional identity,
 *                 and session security details.
 * Architecture: Feature Test (HTTP & Inertia Assertions)
 * ============================================================================
 */

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test unauthenticated access renders profile page with safe guest fallbacks.
     */
    public function test_unauthenticated_user_renders_profile_page(): void
    {
        $response = $this->get('/settings');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/AccountSettings')
            ->has('profile')
            ->where('profile.name', 'Guest User')
        );
    }

    /**
     * Test authenticated user receives profile identity data and department.
     */
    public function test_authenticated_user_receives_profile_identity(): void
    {
        $role = Role::create([
            'name' => 'iqa_staff',
            'display_name' => 'IQA Staff',
            'description' => 'Quality Assurance Officer',
        ]);

        $user = User::create([
            'name' => 'Quality Assurance Officer',
            'email' => 'qa.officer@bicol-u.edu.ph',
            'status' => 'active',
        ]);
        $user->roles()->attach($role->id);

        $response = $this->actingAs($user)->get('/settings');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/AccountSettings')
            ->where('profile.name', 'Quality Assurance Officer')
            ->where('profile.email', 'qa.officer@bicol-u.edu.ph')
            ->where('profile.role', 'iqa_staff')
            ->where('profile.department', 'Internal Quality Assurance Office')
            ->where('profile.status', 'active')
        );
    }

    /**
     * Test authenticated user receives last sign-in timestamp and IP address.
     */
    public function test_authenticated_user_receives_session_info(): void
    {
        $user = User::create([
            'name' => 'Session Test User',
            'email' => 'session.user@bicol-u.edu.ph',
            'status' => 'active',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'auth.login',
            'target_type' => User::class,
            'target_id' => (string) $user->id,
            'ip_address' => '192.168.1.150',
            'details' => ['provider' => 'google'],
        ]);

        $response = $this->actingAs($user)->get('/settings');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Auth/AccountSettings')
            ->where('profile.email', 'session.user@bicol-u.edu.ph')
            ->has('profile.last_sign_in')
            ->has('profile.ip_address')
        );
    }
}
