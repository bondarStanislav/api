<?php
require_once 'wp-load.php';

global $wpdb;

$logina = "m@devurai.com";
$passa = "Devurai666";
$api_server_id = 'wb-Bryt-Dtrgnt-50';
$url_auth = "https://snc.api.sellercloud.com/rest/api/token";
$url_inventory = "https://snc.api.sellercloud.com/rest/api/Inventory?model.inventoryID=S-MS-K-WHT%2CMYKYTATE&warehouseID=205";
//https://api.sellercloud.com/api/server-by-team/?team=205
//https://snc.cwa.api.sellercloud.com/rest/api/token

//Return TOKEN
function autentificateSC($url, $login, $pass) {

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
      "Content-Type: application/json",
      "Accept: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = <<<DATA
    {  
      "Username": "m@devurai.com",  
      "Password": "Devurai666",  
    }
    DATA;

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // allow redirects 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($curl, CURLOPT_MAXREDIRS,5); // return into a variable
    $resp = curl_exec($curl);

    $err = curl_error($curl);

    curl_close($curl);

    $decoded_token = json_decode($resp);
    $token = $decoded_token->access_token;
    if ( $err ) {
        echo "cURL Error #:" . $err;
    } 
    else {
		echo $token;
        return $token;
    }
}

//Return UPC's
function get_api_upc( $url_inventory, $token ) {

    $curl2 = curl_init($url_inventory);
    curl_setopt($curl2, CURLOPT_URL, $url_inventory);
    curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
    "Accept: application/json",
    "Authorization: Bearer {$token}",
    );

    curl_setopt($curl2, CURLOPT_HTTPHEADER, $headers);
    //for debug only!
    curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl2);
    $err = curl_error($curl2);

    curl_close($curl2);
    $get_api_arr = json_decode($resp);
    return $get_api_arr;

}

function wp_db_select_array( $wpdb ) {

    $select_arr = array();

    $emailusers = $wpdb->get_results(
        "SELECT meta.post_id, meta.meta_value " .
        "FROM $wpdb->postmeta AS meta " .
        "INNER JOIN $wpdb->posts AS post ON post.ID = meta.post_id " .
        "WHERE post.post_type = 'product' AND meta.meta_key = '_sku';"
    );

    if( $wpdb->last_error !== '' ) :
        $wpdb->print_error();
    endif;

    foreach ( $emailusers as $user ) {
        $post_id    = $user->post_id;
        $meta_value = $user->meta_value;
        $select_arr[$post_id] = $meta_value;
    }
    return $select_arr;
}

function wp_db_update( $wpdb, $post_id, $upc ) {
    $wpdb->update( $wpdb->postmeta,
        [ 'meta_key' => '_woosea_upc', 'meta_value' => $upc ],
        [ 'post_id' => $post_id , 'meta_key' => '_woosea_upc']
    );
}

//Get Access Token
$token = autentificateSC($url_auth, $logina, $passa);
//UPC list
$get_api_arr = get_api_upc( $url_inventory, $token );

//
$upc_array = $get_api_arr->Items;
echo '<pre>';
//var_dump($upc_array);
echo'</pre>';

foreach ($upc_array as $upc) {
    echo '<pre>';
    var_dump($upc);
    echo'</pre>';
}
//$select_db_arr = wp_db_select_array( $wpdb );

echo '<pre>';
//print_r($select_api_arr);
echo'</pre>';

//wp_db_update($wpdb, 1, 1);
