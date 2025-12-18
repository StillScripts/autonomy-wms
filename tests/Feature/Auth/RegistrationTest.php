<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    // Get the newly created user
    $user = \App\Models\User::where('email', 'test@example.com')->first();
    
    // Verify personal organisation was created
    $organisation = $user->organisations()->first();
    expect($organisation)->not->toBeNull();
    expect($organisation->name)->toBe("Test User's Organisation");
    expect($organisation->personal_organisation)->toBeTrue();

    // Verify membership was created with correct role
    $membership = $organisation->users()->where('user_id', $user->id)->first();
    expect($membership->pivot->role)->toBe(\App\Models\Membership::ROLE_OWNER);
});