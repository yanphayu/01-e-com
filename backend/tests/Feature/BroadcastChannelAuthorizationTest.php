<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BroadcastChannelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_authorize_their_own_private_channel(): void
    {
        $user = $this->makeUser();

        Sanctum::actingAs($user);

        $this->postJson('/broadcasting/auth', [
            'channel_name' => 'App.Models.User.'.$user->id,
            'socket_id' => '123456.789012',
        ])->assertOk()->assertJsonStructure(['auth']);
    }

    public function test_user_cannot_authorize_another_users_private_channel(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();

        Sanctum::actingAs($user);

        $this->postJson('/broadcasting/auth', [
            'channel_name' => 'App.Models.User.'.$other->id,
            'socket_id' => '123456.789012',
        ])->assertForbidden();
    }

    public function test_conversation_participant_can_authorize_chat_channel(): void
    {
        $user1 = $this->makeUser();
        $user2 = $this->makeUser();

        $conversation = Conversation::create([
            'user1_id' => $user1->id,
            'user2_id' => $user2->id,
        ]);

        Sanctum::actingAs($user1);

        $this->postJson('/broadcasting/auth', [
            'channel_name' => 'chat.'.$conversation->id,
            'socket_id' => '123456.789012',
        ])->assertOk()->assertJsonStructure(['auth']);
    }

    public function test_non_participant_cannot_authorize_chat_channel(): void
    {
        $user1 = $this->makeUser();
        $user2 = $this->makeUser();
        $outsider = $this->makeUser();

        $conversation = Conversation::create([
            'user1_id' => $user1->id,
            'user2_id' => $user2->id,
        ]);

        Sanctum::actingAs($outsider);

        $this->postJson('/broadcasting/auth', [
            'channel_name' => 'chat.'.$conversation->id,
            'socket_id' => '123456.789012',
        ])->assertForbidden();
    }

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ], $overrides));
    }
}
