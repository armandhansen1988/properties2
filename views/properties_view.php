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
    <div class="row">
        <div class="col-md-12">
            <div id="map"></div>
        </div>
        <div class="col-md-12 mt-2">
            <table class="table table-striped small">
                <tr>
                    <td colspan="2" align="center"><img src="<?php print $image_url; ?>" width="100%" alt="Property" /></td>
                </tr>
                <tr>
                    <th scope="col">Price</th>
                    <td><?php print number_format($price, 0)." GBP"; ?></td>
                </tr>
                <tr>
                    <th scope="col">Town</th>
                    <td><?php print $town; ?></td>
                </tr>
                <tr>
                    <th scope="col">County</th>
                    <td><?php print $county; ?></td>
                </tr>
                <tr>
                    <th scope="col">Country</th>
                    <td><?php print $country; ?></td>
                </tr>
                <tr>
                    <th scope="col">Description</th>
                    <td><?php print $description; ?></td>
                </tr>
                <tr>
                    <th scope="col">Number of Bedrooms</th>
                    <td><?php print $num_bedrooms; ?></td>
                </tr>
                <tr>
                    <th scope="col">Number of Bathrooms</th>
                    <td><?php print $num_bathrooms; ?></td>
                </tr>
                <tr>
                    <th scope="col">Property Type</th>
                    <td><?php print $property_type_title; ?></td>
                </tr>
                <tr>
                    <th scope="col">Property Description</th>
                    <td><?php print $property_type_description; ?></td>
                </tr>
                <tr>
                    <th scope="col">Coordinates</th>
                    <td><?php print $latitude.", ".$longitude; ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php include_once("footer.php"); ?> 