<?php

namespace App\Support;

use App\Models\SiteSetting;

class SiteSettings
{
    public static function defaults(): array
    {
        return [
            'store_name' => 'Aurevina',
            'phone_primary' => '+62 812 3456 7890',
            'phone_secondary' => '+62 812 9876 5432',
            'whatsapp' => '6281298765432',
            'email' => 'info@aurevina.com',
            'address' => 'Jakarta, Indonesia',
            'map_url' => 'https://www.google.com/maps/search/?api=1&query=Jakarta%2C%20Indonesia',
            'instagram_url' => 'https://www.instagram.com/',
            'x_url' => '',
            'telegram_url' => 'https://t.me/',
            'business_hours' => "Senin - Jumat: 09:00 - 18:00\nSabtu: 10:00 - 16:00\nMinggu: Libur",
            'about_headline' => 'Hijab premium yang dibuat untuk keseharian yang anggun, nyaman, dan percaya diri.',
            'about_image_url' => '',
            'about_us' => 'Koleksi hijab dan busana muslim premium dengan warna elegan, bahan nyaman, dan desain modern untuk keseharian.',
            'privacy_policy' => "Kebijakan Privasi\n\nKami menjaga data pelanggan seperti nama, email, nomor telepon, alamat pengiriman, dan data transaksi hanya untuk kebutuhan layanan toko, pemrosesan pesanan, komunikasi, dan peningkatan pengalaman belanja.",
            'terms_conditions' => "Syarat & Ketentuan\n\nDengan berbelanja di Aurevina, pelanggan menyetujui bahwa data pesanan yang diberikan benar, pembayaran dilakukan sesuai instruksi, dan proses pengiriman mengikuti ketersediaan stok serta layanan ekspedisi.",
            'how_to_order_title' => 'Cara Order di Aurevina',
            'how_to_order_description' => 'Ikuti langkah berikut agar pesanan, pembayaran, dan pengiriman berjalan rapi dari website.',
            'how_to_order_steps' => "Pilih Produk|Buka halaman produk, pilih hijab atau busana yang kamu suka, lalu lihat detail warna, harga, stok, dan ulasan.\nMasukkan Keranjang|Di halaman detail produk, pilih varian warna dan jumlah. Setelah itu klik Tambah ke Keranjang.\nCheckout & Pilih Alamat|Login terlebih dahulu. Kamu bisa memakai alamat tersimpan atau membuat alamat pengiriman baru.\nBayar dan Upload Bukti|Pilih metode pembayaran, transfer sesuai total belanja, lalu upload bukti pembayaran di halaman checkout.\nPantau Pesanan|Setelah pembayaran dikirim, pesanan masuk ke admin. Status pesanan bisa dilihat di halaman Pesanan Saya.",
        ];
    }

    public static function all(): array
    {
        $settings = SiteSetting::query()->pluck('value', 'key')->all();

        return array_merge(self::defaults(), $settings);
    }

    public static function save(array $data): array
    {
        $allowedKeys = array_keys(self::defaults());

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $data)) {
                SiteSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $data[$key] ?? '']
                );
            }
        }

        return self::all();
    }
}
