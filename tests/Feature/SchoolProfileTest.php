<?php

use App\Models\SchoolProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('super admin can view the school information page', function () {
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->get(route('sekolah.edit'));

    $response->assertStatus(200);
    $response->assertSee('Informasi Sekolah');
    $response->assertSee('Alamat Sekolah');
    $response->assertSee('Kontak');
});

test('super admin can update school identity address and contact', function () {
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->put(route('sekolah.update'), [
        'name' => 'SMK Negeri 1 Contoh',
        'npsn' => '12345678',
        'level' => 'SMK',
        'headmaster_name' => 'Budi Santoso',
        'address' => 'Jl. Pendidikan No. 1',
        'city' => 'Bandung',
        'province' => 'Jawa Barat',
        'postal_code' => '40123',
        'phone' => '022-1234567',
        'email' => 'info@smkn1contoh.sch.id',
        'website' => 'https://smkn1contoh.sch.id',
        'description' => 'Sekolah unggulan bidang teknologi.',
    ]);

    $response->assertRedirect(route('sekolah.edit'));

    $school = SchoolProfile::current();
    expect($school->name)->toBe('SMK Negeri 1 Contoh');
    expect($school->address)->toBe('Jl. Pendidikan No. 1');
    expect($school->city)->toBe('Bandung');
    expect($school->full_address)->toBe('Jl. Pendidikan No. 1, Bandung, Jawa Barat, 40123');
    $this->assertDatabaseHas('activity_logs', ['action' => 'school.updated']);
});

test('school name is required when updating school information', function () {
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->from(route('sekolah.edit'))
        ->actingAs($superadmin)
        ->put(route('sekolah.update'), [
            'name' => '',
            'email' => 'bukan-email',
        ]);

    $response->assertSessionHasErrors(['name', 'email']);
});

test('super admin can upload a school logo', function () {
    Storage::fake('public');
    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->put(route('sekolah.update'), [
        'name' => 'SMK Negeri 1 Contoh',
        'logo' => UploadedFile::fake()->image('logo.png'),
    ]);

    $response->assertRedirect(route('sekolah.edit'));

    $school = SchoolProfile::current();
    expect($school->logo)->not->toBeNull();
    expect($school->logo_url)->not->toBeNull();
    Storage::disk('public')->assertExists($school->logo);
});

test('super admin can remove the school logo', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->image('logo.png')->store('school', 'public');
    SchoolProfile::current()->update(['logo' => $path]);

    $superadmin = User::factory()->superadmin()->create();

    $response = $this->actingAs($superadmin)->delete(route('sekolah.logo.destroy'));

    $response->assertRedirect(route('sekolah.edit'));
    expect(SchoolProfile::current()->logo)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('guest can view the public school profile', function () {
    SchoolProfile::current()->update([
        'name' => 'SMA Negeri 2 Contoh',
        'level' => 'SMA',
        'headmaster_name' => 'Siti Aminah',
        'address' => 'Jl. Merdeka No. 10',
        'city' => 'Jakarta',
        'phone' => '021-9876543',
        'description' => 'Sekolah dengan lingkungan belajar yang nyaman.',
    ]);

    $response = $this->get(route('sekolah.public'));

    $response->assertStatus(200);
    $response->assertSee('SMA Negeri 2 Contoh');
    $response->assertSee('Jl. Merdeka No. 10, Jakarta');
    $response->assertSee('Siti Aminah');
    $response->assertSee('Tentang Sekolah');
    $response->assertSee('Bagikan Profil');
});

test('dashboard shows school information as the main card when filled', function () {
    SchoolProfile::current()->update([
        'name' => 'SMK Negeri 1 Contoh',
        'level' => 'SMK',
        'npsn' => '12345678',
        'headmaster_name' => 'Budi Santoso',
        'address' => 'Jl. Pendidikan No. 1',
        'city' => 'Bandung',
        'phone' => '022-1234567',
    ]);

    $murid = User::factory()->murid()->create();

    $response = $this->actingAs($murid)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertSee('school-banner', false);
    $response->assertSee('SMK Negeri 1 Contoh');
    $response->assertSee('Jl. Pendidikan No. 1, Bandung');
    $response->assertSee('Budi Santoso');
    $response->assertSee('022-1234567');
});

test('dashboard does not show the school card while information is empty', function () {
    SchoolProfile::current();

    $murid = User::factory()->murid()->create();

    $response = $this->actingAs($murid)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertDontSee('school-banner', false);
});

test('guru cannot access school information management', function () {
    $guru = User::factory()->guru()->create();

    $this->actingAs($guru)->get(route('sekolah.edit'))->assertStatus(403);
    $this->actingAs($guru)->put(route('sekolah.update'), ['name' => 'Sekolah Palsu'])->assertStatus(403);
});
