<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(): View
    {
        $user = Auth::user()->load(['roles.permissions']);

        return view('admin.profile.show', compact('user'));
    }

    /**
     * Update the user's profile.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Avatar yükleme
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Dosya geçerli mi kontrol et
            if ($file->isValid()) {
                // Eski avatar'ı sil
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Yeni avatar'ı kaydet
                $avatarPath = $file->store('avatars', 'public');
                $validated['avatar'] = $avatarPath;
            }
        } else {
            // Avatar yüklenmemişse, mevcut avatar'ı koru
            unset($validated['avatar']);
        }

        // username kolonu yoksa validation'dan gelen username'i kaldır
        if (! \Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
            unset($validated['username']);
        }

        $user->update($validated);

        return redirect()->route('admin.profile.show')
            ->with('success', 'Profil bilgileriniz başarıyla güncellendi.');
    }
}
