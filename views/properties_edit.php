<?php include_once("header.php"); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" onclick="window.location='/'">Back</button>
        </div>
    </div>
    <div class="py-2 text-center">
        <h2>Armand's Properties</h2>
    </div>
    <?php
    if ($error_type == "success") {
        ?>
            <div class="alert alert-success">
                <strong>Success!</strong> <?php print $error_msg; ?>
            </div>
        <?php
    } elseif ($error_type == "error") {
        ?>
            <div class="alert alert-danger">
                <strong>Error!</strong> <?php print $error_msg; ?>
            </div>
        <?php
    }
    ?>
    <div class="row">
        <div class="col-md-12">
            <input type="text" name="address" id="autocomplete" onFocus="geolocate()" class="form-control" autocomplete="off" value="<?php print $geolocate; ?>" />
            <div id="map" style="display: none;"></div>
        </div>
        <div class="col-md-12 order-md-1">
            <form class="needs-validation" novalidate enctype="multipart/form-data" method="post" action="/properties/edit/<?php print $uuid; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="town">Town</label>
                        <input type="text" class="form-control" name="town" id="town" placeholder="" value="<?php print $town; ?>" required>
                        <div class="invalid-feedback">
                            Please provide a Town Name
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="county">County</label>
                        <input type="text" class="form-control" name="county" id="county" placeholder="" value="<?php print $county; ?>" required>
                        <div class="invalid-feedback">
                            Please provide a County Name
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="country">Country</label>
                        <input type="text" class="form-control" name="country" id="country" placeholder="" value="<?php print $country; ?>" required>
                        <div class="invalid-feedback">
                            Please provide a Country Name
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="postcode">Postal Code</label>
                        <input type="text" class="form-control" name="postcode" id="postcode" placeholder="" value="<?php print $postcode; ?>" required>
                        <div class="invalid-feedback">
                            Please provide the Postal Code
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="displayable_address">Displayable Address</label>
                        <input type="text" class="form-control" name="displayable_address" id="displayable_address" placeholder="" value="<?php print $displayable_address; ?>" required>
                        <div class="invalid-feedback">
                            Please provide a Displayable Address
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="coordinates">Coordinates</label>
                        <input type="text" class="form-control" name="coordinates" id="coordinates" placeholder="" value="<?php print $latitude.", ".$longitude; ?>" required>
                        <div class="invalid-feedback">
                            Please provide the Coordinates. If you dont know it, use the Google Address search at the top
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" name="description" id="description" required><?php print $description; ?></textarea>
                        <div class="invalid-feedback">
                            Please provide a Description
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="image">Image</label>
                        <input type="file" class="form-control" name="image" id="image" placeholder="" value="">
                        <center><img src="<?php print $thumbnail_url; ?>" class="img-thumbnail" alt="Property Thumbnail" /></center>
                        <div class="invalid-feedback">
                            Please provide a Image
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="num_bedrooms">Number of Bedrooms</label>
                        <input type="number" class="form-control" name="num_bedrooms" id="num_bedrooms" placeholder="" value="<?php print $num_bedrooms; ?>" required>
                        <div class="invalid-feedback">
                            Please provide the Number of Bedrooms
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="num_bathrooms">Number of Bathrooms</label>
                        <input type="number" class="form-control" name="num_bathrooms" id="num_bathrooms" placeholder="" value="<?php print $num_bathrooms; ?>" required>
                        <div class="invalid-feedback">
                            Please provide the Number of Bathrooms
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="price">Price</label>
                        <input type="number" class="form-control" name="price" id="price" placeholder="" value="<?php print $price; ?>" required>
                        <div class="invalid-feedback">
                            Please provide the Price
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="property_type">Property Type</label>
                        <select class="form-control" name="property_type" id="property_type" required>
                            <option value="">Select...</option>
                            <?php
                            $property_type = json_decode($property_type_json);
                            for ($i = 0; $i < count($property_type); $i++) {
                                $prop_id = $property_type[$i]->id;
                                $prop_title = $property_type[$i]->title;
                                if ($prop_id == $property_type_id) {
                                    ?>
                                        <option value="<?php print $prop_id; ?>" selected="selected"><?php print $prop_title; ?></option>
                                    <?php
                                } else {
                                    ?>
                                        <option value="<?php print $prop_id; ?>"><?php print $prop_title; ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">
                            Please select a property type
                        </div>
                    </div>
                    <div class="col-md-12 text-center">
                        Sale Type
                    </div>
                    <div class="d-block my-3">
                        <div class="custom-control custom-radio">
                            <input id="sale" name="sale_type" value="sale" type="radio" class="custom-control-input" <?php if ($sale_type == "sale") {print "checked";} ?> required>
                            <label class="custom-control-label" for="sale">Sale</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input id="rent" name="sale_type" value="rent" type="radio" class="custom-control-input" <?php if ($sale_type == "rent") {print "checked";} ?> required>
                            <label class="custom-control-label" for="rent">Rent</label>
                        </div>
                    </div>
                </div>
                <hr class="mb-4">
                <input type="hidden" name="action_type" id="action_type" value="<?php print $type; ?>" />
                <input type="hidden" name="uuid" id="uuid" value="<?php print $uuid; ?>" />
                <button class="btn btn-primary btn-lg btn-block" type="submit">Edit Property</button>
                <hr class="mb-4">
            </form>
        </div>
    </div>
</div>

<?php include_once("footer.php"); ?> 