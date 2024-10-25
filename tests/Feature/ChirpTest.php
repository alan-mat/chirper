<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class ChirpTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_create_chirp(): void
    {
        $user = User::factory()->create();
        $testMessage = "My test chirp";

        $response = $this
            ->actingAs($user)
            ->post("/chirps", [
                "message" => $testMessage,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect("/chirps");

        $user->refresh();

        $this->assertDatabaseCount("chirps", 1);
        $this->assertDatabaseHas("chirps", [
            "message" => $testMessage,
            "user_id" => $user->id
        ]);
    }

    public function test_can_view_all_chirps(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $chirp1 = $user1->chirps()->create([
            "message" => "Chirp One"
        ]);
        $chirp2 = $user1->chirps()->create([
            "message" => "Chirp Two"
        ]);
        $chirp3 = $user2->chirps()->create([
            "message" => "Chirp Three",
        ]);

        $this->assertDatabaseCount("users", 2);
        $this->assertDatabaseCount("chirps", 3);

        $response = $this->actingAs($user1)->get("/chirps");

        $response
            ->assertSessionHasNoErrors()
            ->assertInertia(fn (Assert $page) => $page
                ->component("Chirps/Index")
                ->has("chirps", 3, fn (Assert $page) => $page
                    ->has("message")
                    ->has("user", fn (Assert $page) => $page
                        ->has("id")
                        ->has("name")
                    )
                    ->etc()
                )
            );
    }
}
