<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte;
use App\Models\Brand;

class brand_cmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        for ($i=0; $i < 2000 ; $i++) { 
            $crawler = Goutte::request('GET', 'https://incidecoder.com/brands?offset='.$i);            
            $crawler->filter('.paddingb60 > a')->each(function ($node) {
                $d = [
                    "url" => "https://incidecoder.com".$node->attr('href'),
                    "brand" => $node->text()
                ];
                $insert = Brand::upsert($d, ['url' => $d['url']]);                
                $this->info("https://incidecoder.com".$node->attr('href')." => ".$node->text());
            });
            sleep(2);
        }
        
    }
}
