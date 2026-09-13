<?php

it('uses arabic by default', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('dir="rtl"', false)
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'ar')
            ->where('translations.Budget', 'الميزانية'));
});

it('switches to english', function () {
    $this->from('/')
        ->post('/locale/en')
        ->assertRedirect('/')
        ->assertSessionHas('locale', 'en');

    $this->withSession(['locale' => 'en'])
        ->get('/')
        ->assertSee('dir="ltr"', false)
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'en')
            ->where('translations', []));
});

it('rejects unsupported locales', function () {
    $this->post('/locale/fr')->assertNotFound();
});

it('returns validation messages in arabic', function () {
    $this->post('/login', [])
        ->assertSessionHasErrors(['email' => 'حقل البريد الإلكتروني مطلوب.']);
});
