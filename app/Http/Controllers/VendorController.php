<?php

namespace App\Http\Controllers;


use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;

use App\Vendor;

class VendorController extends Controller {
    public function index() {
        $kunci = "data-vendor";
        /*$cache = getCache($kunci);
        if ($cache != null) {
            return responses($cache, null);
        }*/

        try {
            $vendor = Vendor::select([
                'mst_vendor.id',
                'mst_vendor.name',
                'mst_pricelist.id_vendor',
                'mst_pricelist.id_product'
            ])
            ->leftJoin('mst_pricelist', 'mst_pricelist.id_vendor',  '=','mst_vendor.id')
            ->get();
//print_r ($vendor); die;
            $myData = [];
            foreach ($vendor as $key => $data) {
                $myData[$data['name']][$key] = $data->id_product;
            }
           // print_r ($myData); die;

            $output = [];
            foreach ($myData as $keys => $values) {
                print_r(serialize($values));die;
                if (!empty($values)) {
                    $client = new Client();
                    $result = $client->request('POST', 'http://localhost:8000/product', [
                        'form_params' =>[
                            'id' => serialize($values)
                        ]
                    ])->getBody()->getContents();
                    $result = json_decode($result, true);
                    $output[$keys] = $result['data'];
                }
            }

            //print_r ($output);

            //setCache($kunci, json_encode($dataVendor));
            return responses($output, null);

        } catch (QueryException $th) {
            return response()->json($th);
        }
    }
}
