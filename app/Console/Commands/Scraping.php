<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// use App\Console\Commands\GoutteFacade;
use Weidner\Goutte\GoutteFacade as GoutteFacade;

class Scraping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:scraping';

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
        $goutte = GoutteFacade::request('GET', 'https://www.mercari.com/jp/');
        // echo "hoge";
        // dd($goutte);
        // \Log::debug($goutte);
        // $goutte->filter('ul#s-results-list-atf')->each(function ($ul) {
        //     $ul->filter('li')->each(function ($li) {
        //         dd($li);
        //     });
        // });
    }
}