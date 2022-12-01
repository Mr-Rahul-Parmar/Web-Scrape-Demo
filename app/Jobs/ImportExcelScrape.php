<?php

namespace App\Jobs;

use App\Models\WebScraper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

class ImportExcelScrape implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (isset($this->data) && !empty($this->data)) {
            foreach ($this->data as $key => $data) {
                $url = $data;
                $curl_handle = curl_init();
                curl_setopt($curl_handle, CURLOPT_URL, $url);
                curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
                $query = curl_exec($curl_handle);

                $crawler = new Crawler($query);
                //title
                $param['title'] = $crawler->filter('div.h3U6cGyEUf76tvCpYisik')->text();
                //address
                $param['location'] = $crawler->filter('div.h3U6cGyEUf76tvCpYisik')->text();

                //image
                if ($crawler->filter('meta[property="og:image"]')->count() > 0) {
                    $proImage = $crawler->filter('meta[property="og:image"]')->each(function (Crawler $des) {
                        return $des->attr('content');
                    });
                    $param['propertyImages'] = implode(',', $proImage);
                }

//        //letting details
//        $crawler->filter('div._21Dc_JVLfbrsoEkZYykXK5 dl._2E1qBJkWUYMJYHfYJzUb_r div._2RnXSVJcWbWv4IpBC1Sng6')->each(function (Crawler $index) {
//            return $this->letting[$index->filter('div._2RnXSVJcWbWv4IpBC1Sng6 > dt')->text()] = $index->filter('div._2RnXSVJcWbWv4IpBC1Sng6 > dd')->text();
//        });
//        $param['letting'] = $this->letting;

                //Deposit
                $depositArray = $crawler->filter('div._21Dc_JVLfbrsoEkZYykXK5 dl._2E1qBJkWUYMJYHfYJzUb_r div._2RnXSVJcWbWv4IpBC1Sng6')->each(function (Crawler $node) {
                    return $node->filter('dd')->text();
                });
                $depositString = $depositArray[1];
                $param['deposit'] = str_replace("A deposit provides security for a landlord against damage, or unpaid rent by a tenant.Read more about deposit in our glossary page.", "", $depositString);

                //Bedrooms
                $bedroomArray = $crawler->filter('div._4hBezflLdgDMdFtURKTWh div._3gIoc-NFXILAOZEaEjJi1n')->each(function (Crawler $node) {
                    return $node->filter('div._3OGW_s5TH6aUqi4uHum5Gy')->text();
                });

                //property type
                $param['propertyType'] = $bedroomArray[0];
                //Bedroom
                $param['bedrooms'] = ltrim($bedroomArray[1], '×');
                //Bathroom
                if (count($bedroomArray) < 3) {
                    $param['bathrooms'] = null;
                } else {
                    $param['bathrooms'] = ltrim($bedroomArray[2], '×');
                }
                //Price per month
                $param['pricePerMonth'] = $crawler->filter('div._5KANqpn5yboC4UXVUxwjZ div._3Kl5bSUaVKx1bidl6IHGj7 div._1gfnqJ3Vtd1z40MlC0MzXu span')->text();

                //Price per Week
                $pricePerWeekArray = $crawler->filter('div._5KANqpn5yboC4UXVUxwjZ div._3Kl5bSUaVKx1bidl6IHGj7 div.HXfWxKgwCdWTESd5VaU73')->text();
                $param['pricePerWeek'] = str_replace("The amount per month or week you need to pay the landlord.Read more about property rent in our glossary page.", "", $pricePerWeekArray);

                $crawler->filter('div._4hBezflLdgDMdFtURKTWh div._3gIoc-NFXILAOZEaEjJi1n')->each(function (Crawler $index) {
                    return $this->property[$index->filter('div.IXkFvLy8-4DdLI1TIYLgX')->text()] = $index->filter('div._3OGW_s5TH6aUqi4uHum5Gy ')->text();
                });
                $param['property'] = $this->property;

                //Key features
                $propertyKeyFeatureArray = $crawler->filter('ul._1uI3IvdF5sIuBtRIvKrreQ > li.lIhZ24u1NHMa5Y6gDH90A')->each(function (Crawler $index) {
                    return $index->text();
                });
                $param['propertyKeyFeature'] = implode(",", $propertyKeyFeatureArray);

                //Property Description
                $param['propertyDes'] = $crawler->filter('div.OD0O7FWw1TjbTD4sdRi1_  div.STw8udCxUaBUMfOOZu0iL')->text();

                //Agent Name
                $param['agentName'] = $crawler->filter('div._14g3Gg072iB2RZfaVWqhL3 div.fk2DXJdjfI5FItgj0w4Fd h3._3PpywCmRYxC0B-ShNWxstv')->text();

                //Agent Address
                $param['agentAddress'] = $crawler->filter('div._14g3Gg072iB2RZfaVWqhL3 div.fk2DXJdjfI5FItgj0w4Fd p._1zJF3rohTQLpqNFDBD59qt')->text();

                //Agent Description
                $agentDesArray = $crawler->filter('div._345-szimMh48H78kTr2QMJ')->each(function (Crawler $index) {
                    return $index->text();
                });
                $param['agentDes'] = implode(" ", $agentDesArray);

                //Agent Description
                $agentContactArray = $crawler->filter('div._2WvNKlXUtQjtXXF3acAyQp div._1HfIlGN38D_5t6P1dlQ4pW div._2jlFWdXWxYn37izq_CadDw div._22DMMuqRadeNTtwm_Z5e2E div._3ycD_mvlqA4t74u_G8TZYf a._3E1fAHUmQ27HFUFIBdrW0u')->text();

                $param['agentContact'] = ltrim($agentContactArray, 'Call agent: ');

                $item = WebScraper::create(['p_title' => $param['title'],
                    'location' => $param['location'],
                    'property_type' => $param['propertyType'],
                    'bedroom' => $param['bedrooms'],
                    'bathroom' => $param['bathrooms'],
                    'deposit' => $param['deposit'],
                    'price_p_month' => $param['pricePerMonth'],
                    'price_p_week' => $param['pricePerWeek'],
                    'key_feature' => $param['propertyKeyFeature'],
                    'description' => $param['propertyDes'],
                    'agent_name' => $param['agentName'],
                    'agent_address' => $param['agentAddress'],
                    'agent_contact_no' => $param['agentContact'],
                    'agent_description' => $param['agentDes'],
                    'image' => $param['propertyImages'],
                ]);
            }
        }
    }
}
