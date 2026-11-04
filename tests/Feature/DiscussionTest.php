<?php

use App\Models\DiscussionReply;
use App\Models\DiscussionTopic;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('students can create a topic and reply to each other', function () {
    $mapel = Mapel::factory()->create();
    $author = User::factory()->murid()->create();
    $responder = User::factory()->murid()->create();

    $this->actingAs($author)->post(route('forum.store'), [
        'mapel_id' => $mapel->id,
        'title' => 'Cara mengerjakan limit?',
        'body' => 'Apakah ada tips menyelesaikan soal limit fungsi?',
    ])->assertRedirect();

    $topic = DiscussionTopic::first();

    $this->assertDatabaseHas('discussion_topics', [
        'mapel_id' => $mapel->id,
        'user_id' => $author->id,
        'title' => 'Cara mengerjakan limit?',
    ]);

    $this->actingAs($responder)->post(route('forum.reply', $topic), [
        'body' => 'Coba faktorkan dulu pembilang dan penyebutnya.',
    ])->assertRedirect(route('forum.show', $topic));

    $this->assertDatabaseHas('discussion_replies', [
        'topic_id' => $topic->id,
        'user_id' => $responder->id,
    ]);

    $this->actingAs($responder)
        ->get(route('forum.show', $topic))
        ->assertOk()
        ->assertSee('Cara mengerjakan limit?')
        ->assertSee('Coba faktorkan dulu pembilang dan penyebutnya.');

    $this->actingAs($responder)->get(route('forum.index'))->assertOk()->assertSee('Forum Diskusi');
    $this->actingAs($responder)->get(route('forum.create'))->assertOk()->assertSee('Formulir Topik');
});

test('only the author or superadmin can delete discussion content', function () {
    $topic = DiscussionTopic::factory()->create();
    $reply = DiscussionReply::factory()->create([
        'topic_id' => $topic->id,
        'user_id' => $topic->user_id,
    ]);

    $otherStudent = User::factory()->murid()->create();
    $superadmin = User::factory()->superadmin()->create();

    $this->actingAs($otherStudent)
        ->delete(route('forum.destroy', $topic))
        ->assertForbidden();

    $this->actingAs($otherStudent)
        ->delete(route('forum.reply.destroy', $reply))
        ->assertForbidden();

    $this->actingAs($topic->author)
        ->delete(route('forum.reply.destroy', $reply))
        ->assertRedirect();

    $this->assertDatabaseMissing('discussion_replies', ['id' => $reply->id]);

    $this->actingAs($superadmin)
        ->delete(route('forum.destroy', $topic))
        ->assertRedirect(route('forum.index'));

    $this->assertDatabaseMissing('discussion_topics', ['id' => $topic->id]);
});

test('topic requires a valid subject', function () {
    $student = User::factory()->murid()->create();

    $this->actingAs($student)->post(route('forum.store'), [
        'mapel_id' => 999,
        'title' => 'Topik tanpa mapel',
        'body' => 'Isi diskusi.',
    ])->assertSessionHasErrors('mapel_id');

    $this->assertDatabaseCount('discussion_topics', 0);
});
