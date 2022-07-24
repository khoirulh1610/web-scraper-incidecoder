<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte;
use App\Models\Ingredient;

class ingre_cmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingre';

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
        $ingres = Ingredient::whereNull('description')->get();
        foreach ($ingres as $row) {
            $url = str_replace('/ingredients/ingredients','/ingredients',$row->url);
            $crw_ingre = Goutte::request('GET', $url);
            $crw_ingre->filter('#showmore-section-details')->each(function($node) use($row,$url){
                $description = $node->text();
                Ingredient::where('id',$row->id)->update(['description' => $description]);
                $this->info("Update ".$row->name);
            });
        }
        
    }
}
