<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Menyusun agenda rapat mingguan',
                'Memperbarui panduan kerja tim',
                'Meninjau daftar kebutuhan proyek',
                'Menyiapkan bahan presentasi',
                'Merapikan arsip dokumen bersama',
                'Mencatat hasil evaluasi bulanan',
                'Memeriksa data laporan terbaru',
                'Menyusun jadwal kegiatan tim',
            ]),
            'description' => fake()->optional()->sentence(),
            'is_done' => fake()->boolean(),
            'created_by' => User::factory(),
        ];
    }
}
