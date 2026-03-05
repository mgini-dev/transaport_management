<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function photo(Request $request): StreamedResponse
    {
        $user = $request->user();
        $employee = Employee::query()
            ->select(['id', 'employee_number', 'photo_path'])
            ->whereRaw('LOWER(email) = ?', [strtolower((string) $user?->email)])
            ->first();

        abort_unless($employee && filled($employee->photo_path), 404, 'Profile photo not found.');

        $disk = Storage::disk('private');
        $path = (string) $employee->photo_path;
        abort_unless($disk->exists($path), 404, 'Profile photo file not found.');

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        abort_unless(str_starts_with($mimeType, 'image/'), 415, 'Profile photo is not an image.');

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $filename = 'profile-photo-'.($user?->id ?? 'user').($extension ? '.'.$extension : '');

        return $disk->response(
            $path,
            $filename,
            [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=300',
            ],
            'inline'
        );
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
