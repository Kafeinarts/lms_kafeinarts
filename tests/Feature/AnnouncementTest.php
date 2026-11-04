<?php

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can publish announcement and target audience receives notification', function () {
    $admin = User::factory()->superadmin()->create();
    $guru = User::factory()->guru()->create();
    $student = User::factory()->murid()->create();

    $response = $this->actingAs($admin)->post(route('pengumuman.store'), [
        'title' => 'Libur Semester',
        'body' => 'Kegiatan belajar diliburkan selama satu minggu.',
        'audience' => 'murid',
    ]);

    $announcement = Announcement::first();

    $response->assertRedirect(route('pengumuman.show', $announcement));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('announcements', [
        'user_id' => $admin->id,
        'title' => 'Libur Semester',
        'audience' => 'murid',
    ]);

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $student->id,
        'notifiable_type' => User::class,
        'type' => AnnouncementPublishedNotification::class,
    ]);

    expect($guru->notifications()->count())->toBe(0)
        ->and($student->unreadNotifications()->count())->toBe(1);
});

test('student can read announcements intended for students only', function () {
    $guru = User::factory()->guru()->create();
    $author = User::factory()->guru()->create();
    $student = User::factory()->murid()->create();

    $announcement = Announcement::factory()->create([
        'user_id' => $author->id,
        'audience' => 'murid',
        'title' => 'Pembagian Rapor',
    ]);

    $this->actingAs($student)
        ->get(route('pengumuman.index'))
        ->assertOk()
        ->assertSee('Pembagian Rapor');

    $this->actingAs($student)
        ->get(route('pengumuman.show', $announcement))
        ->assertOk()
        ->assertSee($announcement->body);

    $this->actingAs($guru)
        ->get(route('pengumuman.show', $announcement))
        ->assertForbidden();
});

test('guru cannot target other teachers and cannot delete another author announcement', function () {
    $admin = User::factory()->superadmin()->create();
    $guru = User::factory()->guru()->create();
    $otherGuru = User::factory()->guru()->create();

    $this->actingAs($guru)->post(route('pengumuman.store'), [
        'title' => 'Rapat Guru',
        'body' => 'Rapat internal guru.',
        'audience' => 'guru',
    ])->assertSessionHasErrors('audience');

    $announcement = Announcement::factory()->create([
        'user_id' => $admin->id,
        'audience' => 'all',
    ]);

    $this->actingAs($otherGuru)
        ->delete(route('pengumuman.destroy', $announcement))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('pengumuman.destroy', $announcement))
        ->assertRedirect(route('pengumuman.index'));
});

test('student cannot create announcements', function () {
    $student = User::factory()->murid()->create();

    $this->actingAs($student)->get(route('pengumuman.create'))->assertForbidden();
    $this->actingAs($student)->post(route('pengumuman.store'), [
        'title' => 'Tes',
        'body' => 'Tes',
        'audience' => 'all',
    ])->assertForbidden();
});

test('guru can open announcement create and edit forms', function () {
    $guru = User::factory()->guru()->create();
    $announcement = Announcement::factory()->create(['user_id' => $guru->id]);

    $this->actingAs($guru)
        ->get(route('pengumuman.create'))
        ->assertOk()
        ->assertSee('Formulir Pengumuman');

    $this->actingAs($guru)
        ->get(route('pengumuman.edit', $announcement))
        ->assertOk()
        ->assertSee($announcement->title);
});
