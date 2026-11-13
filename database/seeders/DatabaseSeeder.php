<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $sari = User::factory()->create([
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
            'role' => 'member',
        ]);

        $andi = User::factory()->create([
            'name' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'role' => 'member',
        ]);

        $dewi = User::factory()->create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
            'role' => 'member',
        ]);

        $tasks = [
            [
                'title' => 'Menyusun agenda rapat mingguan',
                'description' => 'Mengumpulkan topik dan menentukan urutan pembahasan rapat.',
                'is_done' => true,
                'created_by' => $admin->id,
                'assignees' => [$sari->id],
            ],
            [
                'title' => 'Memperbarui panduan kerja tim',
                'description' => 'Menyesuaikan panduan dengan alur kerja yang berlaku.',
                'is_done' => false,
                'created_by' => $sari->id,
                'assignees' => [$andi->id, $dewi->id],
            ],
            [
                'title' => 'Meninjau daftar kebutuhan proyek',
                'description' => 'Memeriksa kembali kebutuhan sebelum pekerjaan dimulai.',
                'is_done' => false,
                'created_by' => $andi->id,
                'assignees' => [],
            ],
            [
                'title' => 'Menyiapkan bahan presentasi',
                'description' => 'Merangkum perkembangan proyek untuk dibagikan kepada tim.',
                'is_done' => true,
                'created_by' => $dewi->id,
                'assignees' => [$sari->id],
            ],
            [
                'title' => 'Merapikan arsip dokumen bersama',
                'description' => 'Mengelompokkan dokumen berdasarkan proyek dan tanggal.',
                'is_done' => false,
                'created_by' => $admin->id,
                'assignees' => [$dewi->id],
            ],
            [
                'title' => 'Mencatat hasil evaluasi bulanan',
                'description' => 'Mencatat kesimpulan dan tindak lanjut dari evaluasi tim.',
                'is_done' => true,
                'created_by' => $sari->id,
                'assignees' => [],
            ],
            [
                'title' => 'Memeriksa data laporan terbaru',
                'description' => 'Memastikan angka pada laporan sesuai dengan catatan sumber.',
                'is_done' => false,
                'created_by' => $andi->id,
                'assignees' => [$sari->id, $dewi->id],
            ],
            [
                'title' => 'Menyusun jadwal kegiatan tim',
                'description' => 'Mengatur waktu kegiatan agar tidak berbenturan.',
                'is_done' => false,
                'created_by' => $dewi->id,
                'assignees' => [$andi->id],
            ],
        ];

        foreach ($tasks as $taskData) {
            $assigneeIds = $taskData['assignees'];
            unset($taskData['assignees']);

            $task = Task::factory()->create($taskData);

            if ($assigneeIds !== []) {
                $task->assignees()->attach($assigneeIds);
            }
        }
    }
}
