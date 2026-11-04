<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DiscussionReply;
use App\Models\DiscussionTopic;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    /**
     * Display the discussion forum.
     */
    public function index(Request $request): View
    {
        $topics = DiscussionTopic::query()
            ->with(['mapel', 'author'])
            ->withCount('replies')
            ->when($request->filled('mapel'), fn ($query) => $query->where('mapel_id', $request->integer('mapel')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.forum.index', [
            'topics' => $topics,
            'mapels' => Mapel::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the topic creation form.
     */
    public function create(): View
    {
        return view('pages.forum.create', [
            'mapels' => Mapel::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new discussion topic.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mapel_id' => ['required', 'exists:mapels,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $topic = DiscussionTopic::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        ActivityLog::record(
            'discussion.created',
            "Topik diskusi '{$topic->title}' dibuat di {$topic->mapel?->name}",
            $topic
        );

        return redirect()->route('forum.show', $topic)->with('success', 'Topik diskusi berhasil dibuat.');
    }

    /**
     * Display a discussion topic and its replies.
     */
    public function show(DiscussionTopic $topic): View
    {
        $topic->load(['mapel', 'author', 'replies.author']);

        return view('pages.forum.show', [
            'topic' => $topic,
        ]);
    }

    /**
     * Add a reply to a discussion topic.
     */
    public function reply(Request $request, DiscussionTopic $topic): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ], [
            'body.required' => 'Isi balasan wajib diisi.',
        ]);

        $topic->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return redirect()->route('forum.show', $topic)->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Remove a discussion topic.
     */
    public function destroyTopic(Request $request, DiscussionTopic $topic): RedirectResponse
    {
        $this->ensureCanManage($request->user(), $topic->user_id);

        $title = $topic->title;

        $topic->delete();

        ActivityLog::record('discussion.deleted', "Topik diskusi '{$title}' dihapus", null);

        return redirect()->route('forum.index')->with('success', 'Topik diskusi berhasil dihapus.');
    }

    /**
     * Remove a discussion reply.
     */
    public function destroyReply(Request $request, DiscussionReply $reply): RedirectResponse
    {
        $this->ensureCanManage($request->user(), $reply->user_id);

        $topicId = $reply->topic_id;

        $reply->delete();

        return redirect()->route('forum.show', $topicId)->with('success', 'Balasan berhasil dihapus.');
    }

    /**
     * Abort unless the user owns the content or is a super admin.
     */
    private function ensureCanManage(User $user, int $ownerId): void
    {
        abort_unless(
            $user->isSuperAdmin() || $user->id === $ownerId,
            403,
            'Anda hanya dapat menghapus konten milik sendiri.'
        );
    }
}
