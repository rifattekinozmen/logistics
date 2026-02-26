<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Position::query()->exists()) {
            return;
        }

        $branch = Branch::query()->first();
        if (! $branch) {
            return;
        }

        $positions = [
            'Lojistik Operasyon' => [
                'Lojistik Operasyon Uzmanı',
                'Lojistik Operasyon Sorumlusu',
                'Lojistik Operasyon Müdürü',
            ],
            'Nakliye & Sevkiyat' => [
                'Şoför (Tır)',
                'Şoför (Kamyon)',
                'Sevkiyat Şefi',
                'Sevkiyat Planlama Uzmanı',
            ],
            'Depo & Stok Yönetimi' => [
                'Depo Sorumlusu',
                'Depo Şefi',
                'Depo Personeli',
                'Forklift Operatörü',
                'Stok Kontrol Uzmanı',
            ],
            'Filo Yönetimi' => [
                'Filo Yöneticisi',
                'Filo Sorumlusu',
                'Bakım & Onarım Sorumlusu',
                'Araç Takip Uzmanı',
            ],
            'Planlama & Rota Optimizasyonu' => [
                'Rota Planlama Uzmanı',
                'Operasyon Planlama Uzmanı',
                'Planlama ve Optimizasyon Sorumlusu',
            ],
            'Müşteri İlişkileri & Operasyon Desteği' => [
                'Müşteri Temsilcisi',
                'Operasyon Destek Uzmanı',
                'Çağrı Merkezi Temsilcisi',
            ],
            'Satış & İş Geliştirme' => [
                'Satış Temsilcisi',
                'Kurumsal Satış Uzmanı',
                'İş Geliştirme Uzmanı',
                'Satış Müdürü',
            ],
            'Finans & Muhasebe' => [
                'Muhasebe Uzmanı',
                'Finans Uzmanı',
                'Tahsilat Sorumlusu',
            ],
            'İnsan Kaynakları' => [
                'İK Uzmanı',
                'İK Sorumlusu',
            ],
            'Bilgi Teknolojileri (BT)' => [
                'Yazılım Geliştirici',
                'Sistem Yöneticisi',
                'Uygulama Destek Uzmanı',
            ],
        ];

        foreach ($positions as $departmentName => $names) {
            $department = Department::query()->firstOrCreate(
                ['name' => $departmentName],
                [
                    'branch_id' => $branch->id,
                    'description' => null,
                ],
            );

            foreach ($names as $name) {
                Position::query()->create([
                    'department_id' => $department->id,
                    'name' => $name,
                    'description' => null,
                ]);
            }
        }
    }
}

