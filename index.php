<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Find an ATM</title>
    <style>
    html, #content, .tab-content, .tab-pane, #map {
      height: 100%;
    }
    .btn {
      float:right; 
      margin: 2px
    }
    #footer {
      text-align: center;
    }
    </style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap-theme.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div id="content">
      <?php
      // Google Maps/Places API key
      $apiKey = 'YOUR_API_KEY';
      
      // variables from URL request string
      $latitude = isset($_GET['latitude']) ? trim($_GET['latitude']) : null;
      $longitude = isset($_GET['longitude']) ? trim($_GET['longitude']) : null;
      $selected = isset($_GET['selected']) ? trim($_GET['selected']) : null;
      
      // if no latitude or longitude
      // try to detect using browser geolocation
      if (empty($latitude) || empty($longitude)) {
      ?>
      <script>
      $(document).ready(function() {

        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(handle_geo_query, handle_error);
        } 
    
        function handle_geo_query(location) {
          window.location = '?latitude=' + location.coords.latitude + '&longitude=' + location.coords.longitude;
        }
        
        function handle_error(e) {
          alert('An error occurred during geo-location.');
        }
      });
      </script>       
      <?php
      exit;
      }
      
      // create API request URLs with required parameters 
      $placesApiUrl = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?key=' . $apiKey . '&location=' . sprintf('%f,%f', $latitude, $longitude) . '&rankby=distance&types=atm';  
      $mapsApiUrl = 'https://maps.googleapis.com/maps/api/staticmap?key=' . $apiKey . '&size=640x480&maptype=roadmap&scale=2&markers=color:green|' . sprintf('%f,%f', $latitude, $longitude);
      
      // send request to Places API (for collection of ATMs or single selected ATM)    
      $ch = curl_init();
      curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => 1,  
        CURLOPT_SSL_VERIFYHOST => 2, 
        CURLOPT_URL => $placesApiUrl,
      ));
      $places = json_decode(curl_exec($ch));
      curl_close($ch);
      $results = isset($places->result) ? $places->result : $places->results;
      ?>

      <?php if (count($results) && empty($selected)): ?>
      <div class="alert alert-success" role="alert"><?php echo count($results); ?> ATM(s) found.</div>
      <?php elseif (count($results) && !empty($selected)): ?>
      <div class="alert alert-success" role="alert">Selected ATM found.</div>
      <?php else: ?>
      <div class="alert alert-warning" role="alert">No ATM(s) found.</div>    
      <?php endif; ?>
      
      <ul class="nav nav-tabs" role="tablist">
        <li class="active"><a href="#list" data-toggle="tab">List</a></li>
        <li><a href="#map" data-toggle="tab">Map</a></li>
        <a href="index-js.php" class="btn btn-primary">Interactive Version</a>
        <a href="index.php" class="btn btn-success">Refresh</a>
      </ul>
      
      <div class="tab-content">
      
        <!-- list tab -->
        <div role="tabpanel" class="tab-pane active" id="list">
          <ul class="list-group">
          <?php 
          // iterate over list of ATMs returned
          // generate data for map markers
          // display name, label and location for each ATM
          $c = 65;
          foreach ($results as $place) {
            $label = chr($c);
            if (!empty($selected)) {
              $selArr = explode(':', $selected);
              $label = $selArr[0];
              $selectedId = $selArr[1];
              if ($place->place_id != $selectedId) {
                continue;
              }
            }
            $markers[] = "markers=color:red|label:" . $label . "|" . $place->geometry->location->lat . "," . $place->geometry->location->lng;
            $c++;
          ?>
            <li class="list-group-item">
              <h3>
                <span class="label label-danger"><?php echo $label; ?></span> 
                <span class="label label-default"><?php echo $place->name; ?></span>
              </h3>
              <p><?php echo $place->vicinity; ?></p>
              <a href="?latitude=<?php echo $latitude; ?>&longitude=<?php echo $longitude; ?>&selected=<?php echo $label; ?>:<?php echo $place->place_id; ?>#map">Pinpoint on map</a>
            </li>
          <?php 
          }
          ?>
            <li class="list-group-item">
              <?php echo implode(',', $places->html_attributions); ?>
            </li>
          </ul>  
        </div>
        
        <!-- map tab -->
        <div role="tabpanel" class="tab-pane" id="map">
          <img class="img-responsive" id="mapImage" src="<?php echo $mapsApiUrl; ?>&<?php echo implode('&', $markers); ?>" />         
        </div>

      </div>
      
      <div id="footer">
        <img src="powered-by-google-on-white.png" /> <br />
        <a href="terms.html">Legal Notices</a>
      </div>        
    </div>
    
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>    
    <script src="app.js"></script>    
  </body>
</html>