<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte;
use App\Models\Produk;
use App\Models\Brand;
use App\Models\Ingredient;
use App\Models\ProdukIngredient;
use Config;

class produk_cmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'produk';

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
        $brand = Brand::where('status','<=',1)->get();
        foreach ($brand as $b) {
           $brand_id = $b->id;
           $brand_name = $b->brand;
           Config::set('brand_id', $brand_id);
           Config::set('brand_name', $brand_name);

           Brand::where('id',$brand_id)->update(['status' => 1]);
           $crawler = Goutte::request('GET', $b->url);
            // get Produk
            $crawler->filter('.paddingb60 > a')->each(function ($node) {
                $produk_url = "https://incidecoder.com".$node->attr('href');
                $produk_title = $node->text();       
                Config::set('produk_url', $produk_url);
                Config::set('produk_title', $produk_title);

                // echo "Link Produk : ".$produk_url."\n<br>";
                // echo "Title Produk : ".$produk_title."\n<br>";
            
                $crawler = Goutte::request('GET', $produk_url);
                $d1 = $crawler->filter('#product-title')->each(function ($node) {   
                    $produk = $node->text();             
                    Config::set('produk', $produk);
                    // echo "Nama Produk : ".$produk."\n<br>";;            
                });
                $d2 = $crawler->filter('.prodinfocontainer img')->each(function ($node)  {        
                    $img = $node->attr('src');        
                    Config::set('img', $img);
                    // echo "Link Img Produk : ".$img."\n<br>";
                });

                $d3 = $crawler->filter('.margint23')->each(function ($node) {        
                    $produk_detail = $node->text();
                    Config::set('produk_detail', $produk_detail);
                    // echo "Deskripsi Produk : ".$produk_detail."\n<br><br>";
                });
                $p_id = Produk::insertGetId([                    
                    'url' => Config::get('produk_url'),                     
                    'url_title' => Config::get('produk_title'),
                    'brand' => Config::get('brand_name'),
                    'brand_id' => Config::get('brand_id'),
                    'produk' => Config::get('produk'),
                    'produk_img'=> Config::get('img'),
                    'produk_detail' => Config::get('produk_detail')                    
                ]);
                Config::set('ingredients', null);
                $d4 = $crawler->filter('#ingredlist-short')->each(function ($node) use($crawler,$p_id)  {                 
                    $jing = $node->filter('a')->count();               
                    // echo "Jumlah Ing : ".$jing."\n<br>";
                    // $produk = $ing->text();            
                    $ingredients = [];
                    for ($i=0; $i < $jing; $i++) { 
                        $data = $node->filter('[role=listitem]')->eq($i);
                        $kom_text = "";
                        if($data->count() > 0){
                            $kom_text = $data->text();
                        }
                        $ingredients[] = $kom_text;
                        Config::set('tl_tb'.$p_id, null);
                        Config::set('tips_desc', null);
                        if($kom_url = $data->filter('a')->count() > 0){
                            $kom_url = $data->filter('a')->attr('href');
                            $tips = str_replace("/ingredients/","",$kom_url);                
                            // echo ($i+1).". Kom : ".$kom_text."=>".$kom_url."\n<br>";
                            
                            $crawler->filter('#tt-'.$tips)->each(function ($node) use($tips,$p_id) {
                                $node->filter('.ingred-tooltip-table')->each(function ($node) use($tips,$p_id) {
                                    $kom = $node->filter('tr');
                                    $tip_table = [];
                                    if($kom->count() > 0){
                                        for ($i=0; $i < $kom->count(); $i++) { 
                                            $data = $kom->eq($i);
                                            $p = $data->filter('td')->eq(0)->text();
                                            $v = $data->filter('td')->eq(1)->text();
                                            // echo "tooltip-table : ".$kom_."=>".$kom_url."\n<br>";
                                            $tip_table[] = [
                                                'p' => $p,
                                                'v' => $v
                                            ];
                                        }
                                    }
                                    Config::set('tl_tb'.$p_id, json_encode($tip_table));
                                });

                                $tips = $node->filter('.ingred-tooltip-text');                                
                                if($tips->count() > 0){
                                    $tool_desc = str_replace('[more]','',$tips->text());
                                    Config::set('tips_desc', $tool_desc);
                                    // echo "tooltip-text - ".$tool_desc."\n<br>";
                                }
                            });
                        }
                        
                            
                        
                        ProdukIngredient::upsert([
                            'url'=> 'https://incidecoder.com'.$kom_url,
                            'produk_id' => $p_id,
                            'name' => $kom_text,
                            'tips_desc'=>Config::get('tips_desc'),
                            'tips_tb'=>Config::get('tl_tb'.$p_id)
                        ], ['produk_id' => $p_id, 'url' => 'https://incidecoder.com'.$kom_url]);
                        Ingredient::upsert([
                            'name' => $kom_text,
                            'url' => 'https://incidecoder.com'.$kom_url
                            ], ['url' => 'https://incidecoder.com'.$kom_url]);
                    // echo "<br><br>";
                    }
                    Config::set('ingredients', json_encode($ingredients));
                });

                Produk::where('id',$p_id)->update(['ingredients' => Config::get('ingredients')]);
                // $d4 = $crawler->filter('#ingredlist-short a')->each(function ($node) use($p_id) {     
                //     // dump($node);
                //     $ing_url = "https://incidecoder.com".$node->attr('href');   
                //     $ing_title = $node->text();                    
                //     Config::set('ing_url', $ing_url);
                //     Config::set('ing_title', $ing_title);
                //     // echo "Ing : ".$ing_text."\n<br>";
                //     // echo "Link Ing : ".$ing_url."\n<br>";
                //     $ing = Goutte::request('GET', $ing_url);
                //     $d3 = $ing->filter('.showmore-section')->each(function ($node) {        
                //         $ingre = $node->text();
                //         Config::set('ingre', $ingre);
                //         // echo "Desk Ing : ".$ingre."\n<br>";
                //     });
                //     ProdukIngredient::upsert([
                //         'url'=> Config::get('ing_url'),
                //         'produk_id' => $p_id,
                //         'name' => Config::get('ing_title'),
                //         'description'=>Config::get('ingre')], ['produk_id' => $p_id, 'url' => $ing_url]);
                //     Ingredient::upsert([
                //         'name' => Config::get('ing_title'),
                //         'url' => Config::get('ing_url'),
                //         'description' => Config::get('ingre')], ['name' => $ing_title, 'url' => $ing_url]);
                //     // echo "<br><br>";
                    
                // });

                $this->info('Produk '.Config::get('produk_title').' berhasil ditambahkan');
            });
           Brand::where('id',$brand_id)->update(['status' => 2]);
        }

        
        
    }
}
