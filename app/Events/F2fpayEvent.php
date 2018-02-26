<?php

namespace App\Events;
use Log;
class F2fpayEvent extends Event
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $out_trade_no;
    public function __construct($out_trade_no)
    {
        $this->out_trade_no=$out_trade_no;
       
       
    }
    

}
