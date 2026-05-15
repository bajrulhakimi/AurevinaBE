<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Promo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();

        if ($admin && Blog::count() === 0) {
            $posts = [
                [
                    'title' => 'Cara Memilih Warna Hijab untuk Kulit Sawo Matang',
                    'content' => "Memilih warna hijab yang tepat bisa membuat wajah terlihat lebih segar dan outfit terasa menyatu. Untuk kulit sawo matang, warna seperti mauve, olive, navy, terracotta lembut, dan cream hangat biasanya mudah dipadukan.\n\nMulailah dari warna netral untuk aktivitas harian, lalu tambahkan warna statement untuk acara khusus. Perhatikan juga warna inner dan baju agar keseluruhan tampilan tetap rapi.",
                ],
                [
                    'title' => 'Tips Merawat Pashmina Viscose Tencel agar Awet',
                    'content' => "Pashmina viscose tencel dikenal lembut, jatuh, dan nyaman dipakai. Agar tetap awet, cuci dengan tangan memakai deterjen lembut, hindari perasan kuat, dan jemur di tempat teduh.\n\nSaat menyetrika, gunakan suhu rendah sampai sedang. Simpan pashmina dengan cara dilipat rapi agar serat kain tidak mudah tertarik.",
                ],
                [
                    'title' => 'Inspirasi Outfit Modest untuk Aktivitas Harian',
                    'content' => "Outfit modest harian sebaiknya nyaman, ringan, dan tetap terlihat rapi. Padukan hijab warna netral dengan tunik, kemeja oversize, atau dress sederhana untuk tampilan yang mudah dipakai sepanjang hari.\n\nTambahkan aksesoris kecil seperti bros minimalis atau tas bertekstur untuk memberi detail tanpa membuat tampilan terlalu ramai.",
                ],
            ];

            foreach ($posts as $post) {
                Blog::create([
                    'author_id' => $admin->id,
                    'title' => $post['title'],
                    'slug' => Str::slug($post['title']),
                    'content' => $post['content'],
                    'image' => '',
                    'status' => 'published',
                ]);
            }
        }

        if (Promo::count() === 0) {
            Promo::create([
                'promo_code' => 'AUREVINA10',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'start_date' => now()->subDay()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
            ]);

            Promo::create([
                'promo_code' => 'ONGKIR25',
                'discount_type' => 'fixed',
                'discount_value' => 25000,
                'start_date' => now()->subDay()->toDateString(),
                'end_date' => now()->addWeeks(2)->toDateString(),
            ]);
        }
    }
}
