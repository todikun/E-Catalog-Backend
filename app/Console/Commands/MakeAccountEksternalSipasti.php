<?php

namespace App\Console\Commands;

use App\Models\Accounts;
use App\Models\Users;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAccountEksternalSipasti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:user_sipasti';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make eksternal sipasti account';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dataInsertId = Users::insertGetId([
            'id_roles' => 16,
            'nama_lengkap' => 'eksternal app sipasti',
            'no_handphone' => '-',
            'nik' => '-',
            'satuan_kerja_id' => 1,
            'balai_kerja_id' => 1,
            'status' => 'active',
            'surat_penugasan_url' => '-',
            'email' => 'sipasti@eksternal.com',
            'nrp' => '-',
            'email_verified_at' => now(),
        ]);

        $passEksternal = 'sipasti@eksternal.com';

        $makeAcount = Accounts::create(
            [
                'user_id' => $dataInsertId,
                'username' => 'sipasti@eksternal.com',
                'password' => Hash::make($passEksternal),
            ]
        );

        if ($makeAcount && $dataInsertId) {
            return 'data sukses dibuat';
        } else {
            return 'data gagal ditambahkan';
        }
    }
}
