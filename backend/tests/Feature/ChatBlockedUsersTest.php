<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatBlockedUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_blocked_users(): void
    {
        $user = User::factory()->create();
        $firstBlocked = User::factory()->create();
        $secondBlocked = User::factory()->create();

        Block::create(['blocker_id' => $user->id, 'blocked_id' => $firstBlocked->id]);
        Block::create(['blocker_id' => $user->id, 'blocked_id' => $secondBlocked->id]);

        $response = $this->actingAs($user)->getJson('/api/chat/blocked-users');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_user_cannot_see_blocks_created_by_others(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $blockedUser = User::factory()->create();

        Block::create(['blocker_id' => $otherUser->id, 'blocked_id' => $blockedUser->id]);

        $this->actingAs($user)
            ->getJson('/api/chat/blocked-users')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_user_can_unblock_a_user(): void
    {
        $user = User::factory()->create();
        $blockedUser = User::factory()->create();

        Block::create(['blocker_id' => $user->id, 'blocked_id' => $blockedUser->id]);

        $this->actingAs($user)
            ->deleteJson("/api/chat/blocked-users/{$blockedUser->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('blocks', [
            'blocker_id' => $user->id,
            'blocked_id' => $blockedUser->id,
        ]);
    }

    public function test_unblock_only_removes_own_blocks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $blockedUser = User::factory()->create();

        Block::create(['blocker_id' => $otherUser->id, 'blocked_id' => $blockedUser->id]);

        $this->actingAs($user)
            ->deleteJson("/api/chat/blocked-users/{$blockedUser->id}")
            ->assertOk();

        $this->assertDatabaseHas('blocks', [
            'blocker_id' => $otherUser->id,
            'blocked_id' => $blockedUser->id,
        ]);
    }
}