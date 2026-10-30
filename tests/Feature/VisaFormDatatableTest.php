<?php

use App\Enums\Form\VisaFormStatus;
use App\Helpers\MenuHelper;
use App\Livewire\Form\Datatable;
use App\Models\Form\VisaForm;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => User::ROLE_SUPERADMIN]);
});

test('superadmin can access visa form index route and dashboard', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    $this->actingAs($superadmin)
        ->get(route('visa_form.index'))
        ->assertOk()
        ->assertSeeLivewire(Datatable::class);

    $this->actingAs($superadmin)
        ->get(route('dashboard.index'))
        ->assertOk()
        ->assertSeeLivewire(Datatable::class);
});

test('menu helper displays visa form menu item for authorized superadmin', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    $this->actingAs($superadmin);

    $menuGroups = MenuHelper::getMenuGroups();
    $menuItems = collect($menuGroups)->pluck('items')->flatten(1);

    expect($menuItems->pluck('path'))->toContain('visa_form.index');
});

test('guest or unauthorized user is redirected or denied from visa form index', function () {
    $this->get(route('visa_form.index'))
        ->assertRedirect(route('login'));

    $regularUser = User::factory()->create();

    $this->actingAs($regularUser)
        ->get(route('visa_form.index'))
        ->assertStatus(401);
});

test('datatable renders live visa form records and uses repository', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    $form = VisaForm::factory()->create([
        'nama_lengkap' => 'BUDI SANTOSO',
        'status' => VisaFormStatus::DIAJUKAN,
        'nomor_paspor' => 'B1234567',
        'nomor_coe' => 'COE-999',
        'nama_kaisha' => 'TOYOTA MOTOR CORP',
        'pic_code' => 'PIC-001',
    ]);

    Livewire::actingAs($superadmin)
        ->test(Datatable::class)
        ->assertViewIs('livewire.form.visa-form')
        ->assertSee('BUDI SANTOSO')
        ->assertSee('TOYOTA MOTOR CORP')
        ->assertSee('B1234567')
        ->assertSee($form->nomor_registrasi);
});

test('loadMore increments datatable length by 50 for infinite scroll', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    $component = Livewire::actingAs($superadmin)
        ->test(Datatable::class)
        ->assertSet('length', 50);

    $component->call('loadMore')
        ->assertSet('length', 100);

    $component->call('loadMore')
        ->assertSet('length', 150);
});

test('datatable search filters records and resets length', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(User::ROLE_SUPERADMIN);

    VisaForm::factory()->create([
        'nama_lengkap' => 'ICHIGO KUROSAKI',
        'status' => VisaFormStatus::DIAJUKAN,
    ]);

    VisaForm::factory()->create([
        'nama_lengkap' => 'NARUTO UZUMAKI',
        'status' => VisaFormStatus::DIAJUKAN,
    ]);

    Livewire::actingAs($superadmin)
        ->test(Datatable::class)
        ->set('length', 100)
        ->set('search', 'Ichigo')
        ->assertSet('length', 50)
        ->assertSee('ICHIGO KUROSAKI')
        ->assertDontSee('NARUTO UZUMAKI');
});
