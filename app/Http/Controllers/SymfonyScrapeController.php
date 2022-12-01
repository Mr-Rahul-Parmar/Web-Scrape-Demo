<?php

namespace App\Http\Controllers;

use App\Jobs\ImportExcelScrape;
use App\Models\WebScraper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use DataTables;
use App\Imports\RoomeImport;
use Maatwebsite\Excel\Facades\Excel;

class SymfonyScrapeController extends Controller
{
    public function symfonyScrape(Request $request)
    {

        if ($request->ajax()) {
            $data = WebScraper::select('*');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('image', 'image')
                ->editColumn('image', function ($data) {
                    $imageId = DB::table('web_scrapers')->where('id', $data->id)->value('image');
                    $explode = explode(',', $imageId);
                    if ((is_array($explode))) {
                        $imgs = "";
                        $r['reference_id'] = $data->id;
                        if ($data->id == true) {
                            $imgs .= '<a href="' . $explode[0] . '" data-fancybox="group_' . $r['reference_id'] . '">
                                <img src="' . $explode[0] . '" width="100" />
                            </a>';
                        }
                        $count = 0;
                        foreach ($explode as $image) {
                            if ($count != 0) {
                                $imgs .= "<a data-fancybox='group_" . $r['reference_id'] . "' href='" . $image . "'>
                                        </a>";
                            }
                            $count++;
                        }
                    }
                    return $imgs;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:void(0);" id="delete-compnay" onClick="deleteFunc(' . $row->id . ')" data-toggle="tooltip" data-original-title="Delete" class="delete btn btn-danger">Delete</a>';
                    return $btn;
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }

        return view('symfony-scrape');
    }

    public function rightMoveSiteData(Request $request)
    {
        try {
            $url = $request->url;

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

            return redirect()->back()->with('success', 'Data successfully store.');

        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'Something went to wrong.');
        }
    }

    public function destroy(Request $request)
    {
        $property = WebScraper::where('id', $request->id)->delete();
        $delMessage = "Record Successfully Deleted";
        return [
            'status' => 1,
            'delMessage' => $delMessage
        ];
    }

    public function import(Request $request)
    {
        try {
            Excel::import(new RoomeImport(), request()->file('file'));
            return redirect()->back()->with('success', 'Scrape data successfully import.');
        } catch (\Exception $e) {
            $exception = $e->getMessage();
            return redirect()->back()->with('error', 'Something went to wrong');
        }

    }

}
