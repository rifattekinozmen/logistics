<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_type' => $this->faker->randomElement(['document_expiry', 'maintenance', 'penalty', 'general']),
            'channel' => $this->faker->randomElement(['email', 'sms', 'whatsapp', 'dashboard']),
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'related_type' => null,
            'related_id' => null,
            'status' => $this->faker->randomElement(['pending', 'sent', 'failed']),
            'sent_at' => null,
            'is_read' => false,
            'read_at' => null,
            'metadata' => null,
        ];
    }
}
