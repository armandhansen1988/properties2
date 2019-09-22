<?php

// This can be setup in a cron with settings below. Cron will then run every 10 minutes
// */10 * * * * php /cron/cron.php

set_time_limit(0);
require_once("../classes/Database.php");
require_once("../classes/Functions.php");
$Functions = new Functions();

$url = "http://trialapi.craig.mtcdevserver.com/api/properties?api_key=3NLTTNlXsi6rBWl7nYGluOdkl2htFHug&page[number]=1&page[size]=30";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$response = curl_exec($ch);
curl_close($ch);

$json = json_decode($response);
$last_page = $json->last_page;

for ($k = 1; $k <= $last_page; $k++) {
    $url = "http://trialapi.craig.mtcdevserver.com/api/properties?api_key=3NLTTNlXsi6rBWl7nYGluOdkl2htFHug&page[number]=$k&page[size]=30";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $json = json_decode($response);
    $current_page = $json->current_page;
    $last_page = $json->last_page;
    $data = $json->data;
    $total_data = count($data);
    
    for ($i = 0; $i < $total_data; $i++) {
        $uuid = $Functions->sanatize($data[$i]->uuid);
        $property_type_id = $Functions->sanatize($data[$i]->property_type_id);
        $county = $Functions->sanatize($data[$i]->county);
        $country = $Functions->sanatize($data[$i]->country);
        $town = $Functions->sanatize($data[$i]->town);
        $description = $Functions->sanatize($data[$i]->description);
        $address = $Functions->sanatize($data[$i]->address);
        $image_full = $Functions->sanatize($data[$i]->image_full);
        $image_thumbnail = $Functions->sanatize($data[$i]->image_thumbnail);
        $latitude = $Functions->sanatize($data[$i]->latitude);
        $longitude = $Functions->sanatize($data[$i]->longitude);
        $num_bedrooms = $Functions->sanatize($data[$i]->num_bedrooms);
        $num_bathrooms = $Functions->sanatize($data[$i]->num_bathrooms);
        $price = $Functions->sanatize($data[$i]->price);
        $sale_type = $Functions->sanatize($data[$i]->type);
        $created_at = $Functions->sanatize($data[$i]->created_at);
        $updated_at = $Functions->sanatize($data[$i]->updated_at);
        
        $property_type_id = $Functions->sanatize($data[$i]->property_type->id);
        $property_type_title = $Functions->sanatize($data[$i]->property_type->title);
        $property_type_description = $Functions->sanatize($data[$i]->property_type->description);
        
        $check = DBC::dbsql("SELECT id FROM property_type WHERE id = '$property_type_id';");
        if (DBC::dbrows($check) == 0) {
            DBC::dbsql("INSERT INTO property_type SET 	id = '$property_type_id',
														title = '$property_type_title',
														description = '$property_type_description';");
        }
        
        $check = DBC::dbsql("SELECT * FROM properties WHERE uuid = '$uuid';");
        if (DBC::dbrows($check) == 0) {
            DBC::dbsql("INSERT INTO properties SET 	uuid = '$uuid',
													county = '$county',
													country = '$country',
													town = '$town',
													description = '$description',
													displayable_address = '',
													image_url = '$image_full',
													thumbnail_url = '$image_thumbnail',
													latitude = '$latitude',
													longitude = '$longitude',
													num_bedrooms = '$num_bedrooms',
													num_bathrooms = '$num_bathrooms',
													price = '$price',
													property_type_id = '$property_type_id',
													sale_type = '$sale_type',
													created_at = '$created_at',
													updated_at = '$updated_at';");
        } else {
            $getCheck = DBC::dbfetch($check);
            $status = $getCheck['status'];
            if ($status == 0) {
                DBC::dbsql("UPDATE properties SET 	county = '$county',
													country = '$country',
													town = '$town',
													description = '$description',
													displayable_address = '',
													image_url = '$image_full',
													thumbnail_url = '$image_thumbnail',
													latitude = '$latitude',
													longitude = '$longitude',
													num_bedrooms = '$num_bedrooms',
													num_bathrooms = '$num_bathrooms',
													price = '$price',
													property_type_id = '$property_type_id',
													sale_type = '$sale_type',
													created_at = '$created_at',
													updated_at = '$updated_at'
													WHERE uuid = '$uuid';");
            }
        }
    }
}
