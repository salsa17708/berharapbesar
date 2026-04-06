<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Membuat permissions
        $permissions = [
            'lihat_buku',
            'tambah_buku',
            'edit_buku',
            'hapus_buku',
            'pinjam_buku',
            'kembalikan_buku',
            'lihat_peminjaman',
            'kelola_user'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Membuat roles
        $adminRole = Role::create(['name' => 'admin']);
        $petugasRole = Role::create(['name' => 'petugas']);
        $userRole = Role::create(['name' => 'user']);

        // Assign permissions ke roles
        $adminRole->givePermissionTo(Permission::all());
        
        $petugasRole->givePermissionTo([
            'lihat_buku',
            'tambah_buku',
            'edit_buku',
            'lihat_peminjaman',
            'pinjam_buku',
            'kembalikan_buku'
        ]);
        
        $userRole->givePermissionTo([
            'lihat_buku',
            'pinjam_buku',
            'lihat_peminjaman'
        ]);

        // Membuat users
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@perpustakaan.com',
            'password' => Hash::make('password123'),
            'nim_nis' => 'ADMIN001',
            'no_telepon' => '081234567890',
            'alamat' => 'Kantor Perpustakaan'
        ]);
        $admin->assignRole('admin');

        $petugas = User::create([
            'name' => 'Petugas Perpustakaan',
            'email' => 'petugas@perpustakaan.com',
            'password' => Hash::make('password123'),
            'nim_nis' => 'PTG001',
            'no_telepon' => '081234567891',
            'alamat' => 'Ruang Pelayanan'
        ]);
        $petugas->assignRole('petugas');

        $user = User::create([
            'name' => 'Mahasiswa 1',
            'email' => 'mahasiswa@example.com',
            'password' => Hash::make('password123'),
            'nim_nis' => '20230001',
            'no_telepon' => '081234567892',
            'alamat' => 'Jl. Kampus No. 1'
        ]);
        $user->assignRole('user');
    }
}