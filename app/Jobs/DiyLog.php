<?php

namespace App\Jobs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
class DiyLog extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $id;
    protected $msg = [
        'requester_ip', 'name', 'async_state', 'backurl', 'controller_name', 'request_time', 'response_time', 'response_status', 'msg',
    ];
    public function __construct($id)
    {
        //
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $data = Redis::hmget($this->id,$this->msg);
        $data = array_combine($this->msg,$data);
        $res = DB::table('requester')->insert($data);
        //删除redis寄存的数据信息
        Redis::del($this->id);
    }
}
