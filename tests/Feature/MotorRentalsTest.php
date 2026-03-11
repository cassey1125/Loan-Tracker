<?php

namespace Tests\Feature;

use App\Livewire\MotorRentals;
use App\Models\MotorRental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MotorRentalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_motor_rentals_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/motor-rentals')
            ->assertOk()
            ->assertSee('Motor Rentals');
    }

    public function test_can_create_multi_day_motor_rental(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MotorRentals::class)
            ->set('motor_name', 'Mio 125 - Unit 04')
            ->set('renter_name', 'Juan Dela Cruz')
            ->set('rental_date', '2026-03-11')
            ->set('rental_days', 4)
            ->set('notes', 'Tour rental')
            ->call('saveRental')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('motor_rentals', [
            'motor_name' => 'Mio 125 - Unit 04',
            'renter_name' => 'Juan Dela Cruz',
            'rental_date' => '2026-03-11 00:00:00',
            'rental_days' => 4,
            'rental_end_date' => '2026-03-14 00:00:00',
        ]);
    }

    public function test_selected_date_shows_rentals_active_within_multi_day_range(): void
    {
        $user = User::factory()->create();

        MotorRental::create([
            'motor_name' => 'Click 160 - Unit 02',
            'renter_name' => 'Pedro Santos',
            'rental_date' => '2026-03-11',
            'rental_days' => 4,
            'rental_end_date' => '2026-03-14',
            'notes' => '4 day booking',
        ]);

        Livewire::actingAs($user)
            ->test(MotorRentals::class)
            ->set('selectedDate', '2026-03-13')
            ->assertSee('Click 160 - Unit 02')
            ->assertSee('Mar 11 - Mar 14, 2026')
            ->assertSee('4 days');
    }
}
