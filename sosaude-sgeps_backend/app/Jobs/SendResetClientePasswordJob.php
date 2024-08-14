<?php

namespace App\Jobs;

use App\Models\Cliente;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\SendResetClientePasswordNotification;

class SendResetClientePasswordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $cliente;
    private $password;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Cliente $cliente, $password)
    {
        $this->cliente = $cliente;
        $this->password = $password;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->cliente->notify(new SendResetClientePasswordNotification($this->password));
    }
}
