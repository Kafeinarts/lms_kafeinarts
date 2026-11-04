<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementPublishedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class AnnouncementController extends Controller
{
    /**
     * Display announcements visible to the current user.
     */
    public function index(Request $request): View
    {
        $announcements = Announcement::query()
            ->with('author')
            ->visibleTo($request->user())
            ->latest('published_at')
            ->latest('id')
            ->paginate(10);

        return view('pages.pengumuman.index', [
            'announcements' => $announcements,
        ]);
    }

    /**
     * Show the form for publishing an announcement.
     */
    public function create(Request $request): View
    {
        return view('pages.pengumuman.create', [
            'audiences' => $this->availableAudiences($request->user()),
        ]);
    }

    /**
     * Publish an announcement and notify the target audience.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $announcement = Announcement::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'audience' => $validated['audience'],
            'published_at' => now(),
        ]);

        $this->notifyAudience($announcement);

        ActivityLog::record(
            'announcement.created',
            "Pengumuman '{$announcement->title}' dipublikasikan untuk {$announcement->audience_label}",
            $announcement
        );

        return redirect()
            ->route('pengumuman.show', $announcement)
            ->with('success', 'Pengumuman berhasil dipublikasikan dan notifikasi sudah dikirim.');
    }

    /**
     * Display one announcement.
     */
    public function show(Request $request, Announcement $announcement): View
    {
        abort_unless($announcement->isVisibleTo($request->user()), 403, 'Anda tidak berhak melihat pengumuman ini.');

        $announcement->load('author');

        return view('pages.pengumuman.show', [
            'announcement' => $announcement,
        ]);
    }

    /**
     * Show the form for editing an announcement.
     */
    public function edit(Request $request, Announcement $announcement): View
    {
        $this->ensureCanManage($request->user(), $announcement);

        return view('pages.pengumuman.edit', [
            'announcement' => $announcement,
            'audiences' => $this->availableAudiences($request->user()),
        ]);
    }

    /**
     * Update an announcement.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureCanManage($request->user(), $announcement);

        $validated = $this->validated($request);

        $announcement->update($validated);

        ActivityLog::record(
            'announcement.updated',
            "Pengumuman '{$announcement->title}' diperbarui",
            $announcement
        );

        return redirect()
            ->route('pengumuman.show', $announcement)
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Remove an announcement.
     */
    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureCanManage($request->user(), $announcement);

        $title = $announcement->title;

        $announcement->delete();

        ActivityLog::record('announcement.deleted', "Pengumuman '{$title}' dihapus", null);

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    /**
     * Validate an announcement payload.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'audience' => ['required', 'in:all,guru,murid'],
        ], [
            'audience.in' => 'Target pengumuman tidak valid.',
        ]);

        if (! $request->user()->isSuperAdmin() && $validated['audience'] === 'guru') {
            throw ValidationException::withMessages([
                'audience' => 'Hanya Super Admin yang dapat mengirim pengumuman khusus guru.',
            ]);
        }

        return $validated;
    }

    /**
     * Audiences the user is allowed to target.
     *
     * @return array<string, string>
     */
    private function availableAudiences(User $user): array
    {
        return $user->isSuperAdmin()
            ? Announcement::AUDIENCES
            : array_intersect_key(Announcement::AUDIENCES, ['all' => true, 'murid' => true]);
    }

    /**
     * Send a database notification to everyone in the audience.
     */
    private function notifyAudience(Announcement $announcement): void
    {
        $recipients = User::query()
            ->when(
                $announcement->audience !== 'all',
                fn ($query) => $query->where('role', $announcement->audience)
            )
            ->whereKeyNot($announcement->user_id)
            ->get();

        Notification::send($recipients, new AnnouncementPublishedNotification($announcement));
    }

    /**
     * Abort unless the user authored the announcement or is a super admin.
     */
    private function ensureCanManage(User $user, Announcement $announcement): void
    {
        abort_unless(
            $user->isSuperAdmin() || $announcement->user_id === $user->id,
            403,
            'Anda hanya dapat mengubah pengumuman milik sendiri.'
        );
    }
}
