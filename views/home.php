<?php include_once("header.php"); ?>

<div class="container">
    <div class="py-2 text-center">
        <h2>Armand's Properties</h2>
    </div>
    <div class="row">
        <div class="col-md-12 mt-1" align="center">
            <button type="button" class="btn btn-primary" style="width: 200px;" onclick="window.location='/properties/add'">Add Property</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="row">
    <div class="col-md-12" align="center">
        <li class="fa fa-chevron-left mr-5" style="font-size: 20px; cursor: pointer;" onClick="window.location='/properties/view/<?= $back_from; ?>/<?= $back_to; ?>';"> <span style="font-size: 14px;">Previous Page</span></li>
        <?php
        if ($hideNext == false) {
            ?>
                    <span style="font-size: 14px; cursor: pointer;" onClick="window.location='/properties/view/<?= $forward_from; ?>/<?= $forward_to; ?>';">Next Page <li class="fa fa-chevron-right mr-2" style="font-size: 20px;"></li></span>
            <?php
        }
        ?>
    </div>
</div>
<div class="row">
    <?php
    $property_type = json_decode($property_type_json, true);
    $properties = json_decode($properties_json);
    for ($i = 0; $i < count($properties); $i++) {
        $uuid = $properties[$i]->uuid;
        $county = $properties[$i]->county;
        $country = $properties[$i]->country;
        $town = $properties[$i]->town;
        $thumbnail_url = $properties[$i]->thumbnail_url;
        $price = $properties[$i]->price;
        $num_bedrooms = $properties[$i]->num_bedrooms;
        $num_bathrooms = $properties[$i]->num_bathrooms;
        $property_type_id = $properties[$i]->property_type_id;
        $sale_type = $properties[$i]->sale_type;
        foreach ($property_type as $key => $value) {
            if ($value['id'] == $property_type_id) {
                $property_type_title = $value['title'];
            }
        }
        
        ?>
            <div class="col-md-3 mt-2 pt-2 prop_hover">
                <div class="row" style="font-size: 12px;">
                    <div class="col-md-12 mt-2">
                        <img src="<?php print $thumbnail_url; ?>" width="100%" class="img-thumbnail" alt="Property Thumbnail" />
                    </div>
                    <div class="col-md-12" style="height: 80px;">
                        <?php print $town."<br />".$county."<br />".$country; ?>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-5"><strong>Price</strong></div>
                            <div class="col-md-7">: <?php print number_format($price, 0); ?> GBP</div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-5"><strong>Prop Type</strong></div>
                            <div class="col-md-7">: <?php print $property_type_title; ?></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-5"><strong>Bedrooms</strong></div>
                            <div class="col-md-7">: <?php print $num_bedrooms; ?></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-5"><strong>Bathrooms</strong></div>
                            <div class="col-md-7">: <?php print $num_bathrooms; ?></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-5"><strong>Type</strong></div>
                            <div class="col-md-7">: <?php print ucwords($sale_type); ?></div>
                        </div>
                    </div>
                    <div class="col-md-12 mt-2 mb-2">
                        <div class="row">
                            <div class="col-md-4" align="center"><li class="fa fa-eye" style="font-size: 30px; cursor: pointer;" onClick="window.location='/properties/singleview/<?php print $uuid; ?>';"></li></div>
                            <div class="col-md-4" align="center"><li class="fa fa-pencil-square" style="font-size: 30px; cursor: pointer;" onClick="window.location='/properties/edit/<?php print $uuid; ?>';"></li></div>
                            <div class="col-md-4" align="center"><li class="fa fa-trash" style="font-size: 30px; cursor: pointer;" data-toggle="modal" data-target="#confirmDelete" onClick="$('#delete_uuid').val('<?php print $uuid; ?>');"></li></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }
    ?>
</div>
        </div>
    </div>
    <div class="modal" id="confirmDelete">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirm...</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this property from the listings?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="confirmDelete();">Confirm</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <input type="hidden" id="delete_uuid" value="" />
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once("footer.php"); ?>   