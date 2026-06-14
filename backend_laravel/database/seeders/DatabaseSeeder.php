<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Layanan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed data identik dengan database.sql
     */
    public function run(): void
    {
        // ─── USERS ──────────────────────────────────────────────────────────
        // Password: admin123
        User::firstOrCreate(
            ['email' => 'admin@laundryku.com'],
            [
                'nama' => 'Administrator',
                'no_telp' => '08123456789',
                'alamat' => 'Pusat LaundryKu',
                'username' => 'admin',
                'password' => 'admin123',
                'role' => 'ADMIN',
            ]
        );

        // Password: kurir123
        User::firstOrCreate(
            ['email' => 'kurir@laundryku.com'],
            [
                'nama' => 'Driver Laundry',
                'no_telp' => '081222333444',
                'alamat' => 'Jl. Pengiriman No. 1',
                'username' => 'kurir',
                'password' => 'kurir123',
                'role' => 'KURIR',
            ]
        );

        // Password: user123
        User::firstOrCreate(
            ['email' => 'budi@gmail.com'],
            [
                'nama' => 'Budi Pelanggan',
                'no_telp' => '08555666777',
                'alamat' => 'Perumahan Elite Blok A',
                'username' => 'budi',
                'password' => 'user123',
                'role' => 'USER',
            ]
        );

        // ─── LAYANAN ────────────────────────────────────────────────────────
        $layananData = [
            ['nama_layanan' => 'Laundry Reguler', 'deskripsi' => 'Cuci lipat rapi, pengerjaan 2-3 hari.', 'harga_per_kg' => 6000, 'estimasi_hari' => 3],
            ['nama_layanan' => 'Laundry Express', 'deskripsi' => 'Cuci lipat rapi, pengerjaan 1 hari.', 'harga_per_kg' => 10000, 'estimasi_hari' => 1],
            ['nama_layanan' => 'Cuci Setrika', 'deskripsi' => 'Cuci dan setrika rapi, pengerjaan 2-3 hari.', 'harga_per_kg' => 8000, 'estimasi_hari' => 3],
            ['nama_layanan' => 'Setrika Saja', 'deskripsi' => 'Setrika rapi tanpa cuci.', 'harga_per_kg' => 4000, 'estimasi_hari' => 2],
            ['nama_layanan' => 'Laundry Sepatu', 'deskripsi' => 'Pembersihan khusus untuk berbagai jenis sepatu.', 'harga_per_kg' => 25000, 'estimasi_hari' => 3],
            ['nama_layanan' => 'Laundry Karpet', 'deskripsi' => 'Cuci karpet bersih dan wangi.', 'harga_per_kg' => 15000, 'estimasi_hari' => 5],
        ];

        foreach ($layananData as $data) {
            Layanan::firstOrCreate(
                ['nama_layanan' => $data['nama_layanan']],
                $data
            );
        }
    }
}
