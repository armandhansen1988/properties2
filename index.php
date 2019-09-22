<?php

$config = array();
$config['title'] = "Property Listings";

require_once("classes/Database.php");
require_once("classes/Functions.php");
$Functions = new Functions();

$url = explode("/", ltrim($_SERVER['REQUEST_URI'], "/"));

switch ($url[0]) {
    case "":
        header("location:properties/view/0/0");
        exit;
    case "properties":
        switch ($url[1]) {
            case "":
                include("views/404.php");
                exit;
            case "view":
                $from = $url[2];
                if (empty($from)) {
                    $from = "0";
                }
                $to = $url[3];
                if (empty($to)) {
                    $to = "20";
                }
                
                $properties_json = $Functions->mysql2json("SELECT * FROM properties WHERE status != '3' LIMIT $from, $to;");
                $property_type_json = $Functions->mysql2json("SELECT * FROM property_type ORDER BY id ASC;");
                
                $hideNext = false;
                if (empty(json_decode($properties_json))) {
                    $hideNext = true;
                }
                
                $back_from = $from - 20;
                $back_to = $to - 20;
                $forward_from = $to;
                $forward_to = $to + 20;
                if ($back_from < 0) {
                    $back_from = "0";
                }
                
                include("views/home.php");
                exit;
            case "singleview":
                $uuid = $Functions->sanatize($url[2]);
                $property = DBC::dbsql("SELECT * FROM properties WHERE uuid = '$uuid';");
                $getProperty = DBC::dbfetch($property);
                $county = $getProperty['county'];
                $country = $getProperty['country'];
                $town = $getProperty['town'];
                $description = $getProperty['description'];
                $image_url = $getProperty['image_url'];
                $latitude = $getProperty['latitude'];
                $longitude = $getProperty['longitude'];
                $num_bedrooms = $getProperty['num_bedrooms'];
                $num_bathrooms = $getProperty['num_bathrooms'];
                $price = $getProperty['price'];
                $property_type_id = $getProperty['property_type_id'];
                $sale_type = $getProperty['sale_type'];
                
                $proptype = DBC::dbsql("SELECT * FROM property_type WHERE id = '$property_type_id';");
                $getPropType = DBC::dbfetch($proptype);
                $property_type_title = $getPropType['title'];
                $property_type_description = $getPropType['description'];
                
                $lat = $latitude;
                $lng = $longitude;
                $geolocate = "";
                $type = "";
                
                include("views/properties_view.php");
                exit;
            case "add":
                $error_type = "";
                if ($_POST) {
                    $token = $Functions->generateToken();
                    $town = $Functions->sanatize($_POST['town']);
                    $county = $Functions->sanatize($_POST['county']);
                    $country = $Functions->sanatize($_POST['country']);
                    $postcode = $Functions->sanatize($_POST['postcode']);
                    $displayable_address = $Functions->sanatize($_POST['displayable_address']);
                    $coordinates = $Functions->sanatize($_POST['coordinates']);
                    $description = $Functions->sanatize($_POST['description']);
                    $image = basename($_FILES['image']['name']);
                    $num_bedrooms = $Functions->sanatize($_POST['num_bedrooms']);
                    $num_bathrooms = $Functions->sanatize($_POST['num_bathrooms']);
                    $price = $Functions->sanatize($_POST['price']);
                    $property_type = $Functions->sanatize($_POST['property_type']);
                    $sale_type = $Functions->sanatize($_POST['sale_type']);
                    
                    if (empty($town)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Town";
                    } elseif (empty($county)) {
                        $error_type = "error";
                        $error_msg = "Please provide a County";
                    } elseif (empty($country)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Country";
                    } elseif (empty($county) || is_nan($postcode)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Valid Postal Code";
                    } elseif (empty($displayable_address)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Displayable Address";
                    } elseif (empty($coordinates)) {
                        $error_type = "error";
                        $error_msg = "Please provide Coordinates";
                    } elseif (empty($description)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Description";
                    } elseif (empty($num_bedrooms) || is_nan($num_bedrooms)) {
                        $error_type = "error";
                        $error_msg = "No Number of Bedrooms Provided";
                    } elseif (empty($num_bathrooms) || is_nan($num_bathrooms)) {
                        $error_type = "error";
                        $error_msg = "No Number of Bathrooms Provided";
                    } elseif (empty($price) || is_nan($price)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Valid Price";
                    } elseif (empty($property_type)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Property Type";
                    } elseif (empty($sale_type)) {
                        $error_type = "error";
                        $error_msg = "Please provide the Sale Type";
                    } else {
                        $exCoor = explode(", ", $coordinates);
                        $lat = $exCoor[0];
                        $lng = $exCoor[1];
                        $valid_ext = array("jpg", "jpeg", "png");
                        
                        $unix_time = time();
                        $image_url = "uploads/" . time() . "_" . $image;
                        $image_url_ext = strtolower(pathinfo($image_url, PATHINFO_EXTENSION));
                        if (!in_array($image_url_ext, $valid_ext)) {
                            $error_type = "error";
                            $error_msg = "Image can only be jpg, jpeg, or png!";
                        } else {
                            $thumbnail_url = "uploads/thumb_" . time() . "_" . $image;
                            move_uploaded_file($_FILES['image']['tmp_name'], $image_url);
                            $Functions->generateThumbnail($image_url, $thumbnail_url, 100, 40);
                            $save = DBC::dbsql("INSERT INTO properties SET  uuid = '$token',
                                                                            county = '$county',
                                                                            country = '$country',
                                                                            town = '$town',
                                                                            postcode = '$postcode',
                                                                            description = '$description',
                                                                            displayable_address = '$displayable_address',
                                                                            image_url = '/$image_url',
                                                                            thumbnail_url = '/$thumbnail_url',
                                                                            latitude = '$lat',
                                                                            longitude = '$lng',
                                                                            num_bedrooms = '$num_bedrooms',
                                                                            num_bathrooms = '$num_bathrooms',
                                                                            price = '$price',
                                                                            property_type_id = '$property_type',
                                                                            sale_type = '$sale_type',
                                                                            created_at = NOW(),
                                                                            status = '1';");
                            if ($save) {
                                $error_type = "success";
                                $error_msg = "Property added successfully";
                            } else {
                                $error_type = "error";
                                $error_msg = "Unable to update Database. Please try again";
                            }
                        }
                    }
                }
                
                $geolocate = "";
                $type = "add";
                $lat = "";
                $lng = "";
                $property_type_json = $Functions->mysql2json("SELECT * FROM property_type ORDER BY id ASC;");
                include("views/properties_add.php");
                exit;
            case "edit":
                $error_type = "";
                $uuid = $Functions->sanatize($url[2]);
                
                if ($_POST) {
                    $town = $Functions->sanatize($_POST['town']);
                    $county = $Functions->sanatize($_POST['county']);
                    $country = $Functions->sanatize($_POST['country']);
                    $postcode = $Functions->sanatize($_POST['postcode']);
                    $displayable_address = $Functions->sanatize($_POST['displayable_address']);
                    $coordinates = $Functions->sanatize($_POST['coordinates']);
                    $description = $Functions->sanatize($_POST['description']);
                    $image = basename($_FILES['image']['name']);
                    $num_bedrooms = $Functions->sanatize($_POST['num_bedrooms']);
                    $num_bathrooms = $Functions->sanatize($_POST['num_bathrooms']);
                    $price = $Functions->sanatize($_POST['price']);
                    $property_type = $Functions->sanatize($_POST['property_type']);
                    $sale_type = $Functions->sanatize($_POST['sale_type']);
                    
                    if (empty($town)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Town";
                    } elseif (empty($county)) {
                        $error_type = "error";
                        $error_msg = "Please provide a County";
                    } elseif (empty($country)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Country";
                    } elseif (empty($county) || is_nan($postcode)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Valid Postal Code";
                    } elseif (empty($displayable_address)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Displayable Address";
                    } elseif (empty($coordinates)) {
                        $error_type = "error";
                        $error_msg = "Please provide Coordinates";
                    } elseif (empty($description)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Description";
                    } elseif (empty($num_bedrooms) || is_nan($num_bedrooms)) {
                        $error_type = "error";
                        $error_msg = "No Number of Bedrooms Provided";
                    } elseif (empty($num_bathrooms) || is_nan($num_bathrooms)) {
                        $error_type = "error";
                        $error_msg = "No Number of Bathrooms Provided";
                    } elseif (empty($price) || is_nan($price)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Valid Price";
                    } elseif (empty($property_type)) {
                        $error_type = "error";
                        $error_msg = "Please provide a Property Type";
                    } elseif (empty($sale_type)) {
                        $error_type = "error";
                        $error_msg = "Please provide the Sale Type";
                    } else {
                        $exCoor = explode(", ", $coordinates);
                        $lat = $exCoor[0];
                        $lng = $exCoor[1];
                        
                        if ($image != "") {
                            $unix_time        = time();
                            $image_url         = "uploads/" . time() . "_" . $image;
                            $thumbnail_url     = "uploads/thumb_" . time() . "_" . $image;
                            $image_url_ext    = strtolower(pathinfo($image_url, PATHINFO_EXTENSION));
                            if (!in_array($image_url_ext, $valid_ext)) {
                                $error = 2;
                                $error_msg = "Image can only be jpg, jpeg, or png!";
                            } else {
                                move_uploaded_file($_FILES['image']['tmp_name'], $image_url);
                                generateThumbnail($image_url, $thumbnail_url, 100, 40);
                                DBC::dbsql("UPDATE properties SET     image_url = '/$image_url',
                                                                    thumbnail_url = '/$thumbnail_url'
                                                                    WHERE uuid = '$uuid';");
                            }
                        }
                        
                        $update = DBC::dbsql("UPDATE properties SET county = '$county',
                                                                    country = '$country',
                                                                    town = '$town',
                                                                    postcode = '$postcode',
                                                                    description = '$description',
                                                                    displayable_address = '$displayable_address',
                                                                    latitude = '$lat',
                                                                    longitude = '$lng',
                                                                    num_bedrooms = '$num_bedrooms',
                                                                    num_bathrooms = '$num_bathrooms',
                                                                    price = '$price',
                                                                    property_type_id = '$property_type',
                                                                    sale_type = '$sale_type',
                                                                    updated_at = NOW(),
                                                                    status = '1'
                                                                    WHERE uuid = '$uuid';");
                        if ($update) {
                            $error_type = "success";
                            $error_msg = "Property updated successfully";
                        } else {
                            $error_type = "error";
                            $error_msg = "Unable to update Database. Please try again";
                        }
                    }
                }
                
                $property = DBC::dbsql("SELECT * FROM properties WHERE uuid = '$uuid';");
                $getProperty = DBC::dbfetch($property);
                $county = $getProperty['county'];
                $country = $getProperty['country'];
                $town = $getProperty['town'];
                $postcode = $getProperty['postcode'];
                $displayable_address = $getProperty['displayable_address'];
                $description = $getProperty['description'];
                $image_url = $getProperty['image_url'];
                $thumbnail_url = $getProperty['thumbnail_url'];
                $latitude = $getProperty['latitude'];
                $longitude = $getProperty['longitude'];
                $num_bedrooms = $getProperty['num_bedrooms'];
                $num_bathrooms = $getProperty['num_bathrooms'];
                $price = $getProperty['price'];
                $property_type_id = $getProperty['property_type_id'];
                $sale_type = $getProperty['sale_type'];
                
                $proptype = DBC::dbsql("SELECT * FROM property_type WHERE id = '$property_type_id';");
                $getPropType = DBC::dbfetch($proptype);
                $property_type_title = $getPropType['title'];
                $property_type_description = $getPropType['description'];
                if ($displayable_address == "") {
                    $geolocate = "$town, $county, $country";
                } else {
                    $geolocate = $displayable_address;
                }
                
                $property_type_json = $Functions->mysql2json("SELECT * FROM property_type ORDER BY id ASC;");
                include("views/properties_edit.php");
                exit;
            case "delete":
                $data = array();
                $uuid = $Functions->sanatize($url[2]);
                $delete = DBC::dbsql("UPDATE properties SET status = '3' WHERE uuid = '$uuid';");
                if ($delete) {
                    $data['error'] = '1';
                } else {
                    $data['error'] = '2';
                }
                print json_encode($data);
                exit;
            default:
                include("views/404.php");
                exit;
        }
        // no break
    default:
        include("views/404.php");
        exit;
}
