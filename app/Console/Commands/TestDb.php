<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\EchoTest;

class TestDb extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testdb {--queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = $this->options();
        $queueName = $this->option('queue');
        $this->info('hello world!');
        $this->info('中文测试');
        $this->error('something went wrong!');

        $this->info('通过命令行执行队列任务');

        for ($i = 0; $i < 1000; ++$i) {
            $this->dispatch((new EchoTest($i))->onQueue('echo'));
        }



    }
}
