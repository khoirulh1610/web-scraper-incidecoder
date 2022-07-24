<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', function() {
    $crawler = Goutte::request('GET', 'https://incidecoder.com/brands/100-pure');
    // get Produk
    $crawler->filter('.paddingb60 > a')->each(function ($node) {
        $url = "https://incidecoder.com".$node->attr('href');
        $produk = $node->text();        
        echo "Link Produk : ".$url."\n<br>";
        echo "Title Produk : ".$produk."\n<br>";
    
        $crawler = Goutte::request('GET', $url);
        $d1 = $crawler->filter('#product-title')->each(function ($node) {                    
            echo "Nama Produk : ".$node->text()."\n<br>";;            
        });
        $d2 = $crawler->filter('.prodinfocontainer img')->each(function ($node) {        
            $img = $node->attr('src');            
            echo "Link Img Produk : ".$img."\n<br>";
        });

        $d3 = $crawler->filter('.margint23')->each(function ($node) {        
            $produk = $node->text();
            echo "Deskripsi Produk : ".$produk."\n<br><br>";
        });

        // $k =[];
        $d4 = $crawler->filter('#ingredlist-short')->each(function ($node) use($crawler) {                 
            $jing = $node->filter('a')->count();               
            echo "Jumlah Ing : ".$jing."\n<br>";
            // $produk = $ing->text();            
            for ($i=0; $i < $jing; $i++) { 
                $data = $node->filter('[role=listitem]')->eq($i);
                $kom = $data->text();
                if($data->filter('a')->count() > 0){
                    $kom_url = $data->filter('a')->attr('href');
                    $tips = str_replace("/ingredients/","",$kom_url);                
                    echo ($i+1).". Kom : ".$kom."=>".$kom_url."\n<br>";
                    $crawler->filter('#tt-'.$tips)->each(function ($node) use($tips) {
                        $node->filter('.ingred-tooltip-table')->each(function ($node) use($tips) {
                            $kom = $node->filter('tr');
                            if($kom->count() > 0){
                                for ($i=0; $i < $kom->count(); $i++) { 
                                    $data = $kom->eq($i);
                                    $kom_ = $data->filter('td')->eq(0)->text();
                                    $kom_url = $data->filter('td')->eq(1)->text();
                                    echo "tooltip-table : ".$kom_."=>".$kom_url."\n<br>";
                                }
                            }
                        });

                        // if($what_it_does->count() > 0){
                        //     echo "    - ".$what_it_does->text()."\n<br>";
                        // }

                        $tips = $node->filter('.ingred-tooltip-text');
                        if($tips->count() > 0){
                            echo "tooltip-text - ".str_replace('[more]','',$tips->text())."\n<br>";
                        }
                        
                    });
                }
                
            }

            // $tool = $node->filter('span')->attr('data-tooltip-content');   
            // echo "Ing : ".$produk."\n<br>";
            // echo "Link Ing : ".$url."\n<br>";
            // echo "tt : ".$tool."\n<br><hr/>";

            // $ing = Goutte::request('GET', $url);
            // $d3 = $ing->filter('.showmore-section')->each(function ($node) {        
            //     $ingre = $node->text();
            //     echo "Desk Ing : ".$ingre."\n<br>";
            // });
            // echo "<br><br>";
        });
       
        echo "\n<br><br>";
    });
    // return view('welcome');
});


Route::get('/d', function() {
    $crawler = Goutte::request('GET', 'https://incidecoder.com/products/27-degrees-centella-glow-gentle-facial-wash');
    // dd($crawler);
    $d = $crawler->filter('.prodinfocontainer img')->each(function ($node) {        
        $img = $node->attr('src');
        echo $img."\n";
    });

    $d = $crawler->filter('.margint23')->each(function ($node) {        
        $produk = $node->text();
        echo $produk."\n";
    });

    // $d2 = $crawler->filter('#ingredlist-short a')->each(function ($node) {     
    //     // dump($node);
    //     $url = "https://incidecoder.com".$node->attr('href');   
    //     $produk = $node->text();
    //     echo $url." => ".$produk."\n";
    //     $ing = Goutte::request('GET', $url);
    //     $d3 = $ing->filter('.showmore-section')->each(function ($node) {        
    //         $produk = $node->text();
    //         echo $produk."\n";
    //     });
    // });
});

Route::get('/ig', function() {
    $crawler = Goutte::request('GET', 'https://www.instagram.com/explore/tags/surabaya/');
    dd($crawler);
    $d = $crawler->filter('.prodinfocontainer img')->each(function ($node) {        
        $img = $node->attr('src');
        echo $img."\n";
    });

});

