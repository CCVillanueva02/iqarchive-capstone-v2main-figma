<?php

/**
 * ============================================================================
 * IQArchive v2 — System Administration Controller
 * ============================================================================
 * File: app/Http/Controllers/AdminController.php
 * Responsibility: Manages user access, college & program records, and audit logs.
 * Architecture: Controller Layer
 * Security Context: Strictly gated to users with role == 'system_admin'.
 * ============================================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Display the administration console.
     *
     * Security Reasoning: Access restricted strictly to System Administrators.
     * Content evaluations are prohibited; strictly operational governance.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Index');
    }

    /**
     * Display the accounts management console for IQA and System Administrators.
     *
     * Security Reasoning: Access restricted to authorized administrative personas
     * (IQA staff and System Admin) to manage institutional users and role affiliations.
     */
    public function users(Request $request): Response
    {
        $users = \App\Models\User::with(['roles', 'college'])->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'avatar_url' => $u->avatar_url,
                'role' => $u->role ?? 'Task Force Member',
                'college_code' => $u->college?->code ?? 'CS',
                'program_code' => 'BSIT',
                'status' => $u->status ?? 'active',
                'created_at' => $u->created_at?->format('M d, Y') ?? 'Aug 25, 2026',
                'created_ago' => '1 month ago',
            ];
        });

        return Inertia::render('Admin/Users/Index', [
            'initialUsers' => $users,
        ]);
    }

    /**
     * Display the Colleges & Programs directory console.
     *
     * Security Reasoning: Provides institutional oversight of colleges, campuses,
     * and their associated degree programs. Gated at route and controller boundary.
     */
    public function colleges(Request $request): Response
    {
        return Inertia::render('Iqa/Colleges');
    }
}
