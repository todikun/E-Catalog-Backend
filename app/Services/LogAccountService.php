<?php

namespace App\Services;

use App\Models\LogAccount;

class LogAccountService
{
    public function storeLog($data)
    {
        $log = new LogAccount();
        $log->user_id = $data->user_id;
        $log->action = $data->action;
        $log->ip_address = $data->ip_address;
        $log->save();

        return true;
    }
}
