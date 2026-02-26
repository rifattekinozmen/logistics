<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Department::query()->exists()) {
            return;
        }

        $branch = Branch::query()->first();
        if (! $branch) {
            return;
        }

        $names = [
            'Lojistik Operasyon',
            'Nakliye & Sevkiyat',
            'Depo & Stok Yönetimi',
            'Filo Yönetimi',
            'Planlama & Rota Optimizasyonu',
            'Müşteri İlişkileri & Operasyon Desteği',
            'Satış & İş Geliştirme',
            'Finans & Muhasebe',
            'İnsan Kaynakları',
            'Bilgi Teknolojileri (BT)',
        ];

        foreach ($names as $name) {
            Department::query()->create([
                'branch_id' => $branch->id,
                'name' => $name,
                'description' => null,
            ]);
        }
    }
}

