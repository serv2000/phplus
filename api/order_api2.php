<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of order_api
 *
 * @author abod
 */
class order_api {
    //put your code here
    
    
    static function add() {
        global $p;
        $file=HOMEDIR.'json/'. time().'.json';
        file_put_contents($file, $_POST['url']);
        $p['time']= time();
        $orderObj= json_decode($_POST['url']);
        $orderdetails= json_decode($orderObj->ORDER_JSON);
        $userid=$orderdetails->Location->USER_ID;
        $lat=$orderdetails->Location->LATITUDE;
        $lng=$orderdetails->Location->LONGITUDE;
        $products=$orderdetails->products->products;
        $image=$orderdetails->ImageEncode->images;
        $otherimages= json_decode($orderdetails->MultiImages);
        $phone=$orderObj->orderUserPhone;
        $addressOnMap=$orderObj->ADDRESS;
        $address=$orderObj->orderUserAddress;
        $buildingNumber=$orderObj->orderUserBuilderNumber;
        $flatNumber=$orderObj->orderUserFlatNumber;
        $floor=$orderObj->orderUserFloorNumber;
        $name=$orderObj->orderUserName;
        $order=new orders();
        $order->owneruserID=$userid;
        $order->address= json_encode(['address'=>$address,'flat'=>$flatNumber,'building'=>$buildingNumber,'floor'=>$floor,'mapAdress'=>$addressOnMap]);
        $order->createdTime=time();
        $order->name=$name;
        $order->ordreStatus='created';
        $order->placeType=5;
        $order->userLat=$lat;
        $order->userlLong=$lng;
        $order->phone=$phone;
        $pharmacy=new pharmacies();
        $pharmacy->placeLat=$lat;
        $pharmacy->placeLong=$lng;
        $pharmacies=$pharmacy->get_place_by_latlng(20, 1)['result'][0];
        $order->placeID=$pharmacies['placeID'];
        $pharmacy->placeID=$order->placeID;
        $pharmacy->read_row();
        $file=new files($pharmacies['placeImg']);
        $pharmacies['image']=$file->placeOnServer;
        unset($pharmacies['placeImg'],$pharmacies['placeAreaID']);
        
        //$pharmacy->get_access_list();
$order->totalprice= array_sum(array_column($products, "pharmproPrice"));

        
    $order->create();

//retrive the pharmacy data
$pharmacy_order_products=new ordrespharmacydata();
$pharmacy_order_products->ordreID=$order->orderID;
foreach ($products as $prod) {
    $pharmacy_order_products->OrdersPharmacyDataID=0;
    $pharmacy_order_products->poductID=$prod->pharmProID;
    $pharmacy_order_products->quantity=($prod->pharmProQuantity==0 || $prod->pharmProQuantity=='0')?1:$prod->pharmProQuantity;
    $pharmacy_order_products->create();
  //  print_rr($pharmacy_order_products)  ;
}
//print_rr($otherimages);
//add the single image
if(strlen($image)>10)
files::add_order_file($order->orderID, $image);
//add the multi images
if(count($otherimages)>0)
foreach ($otherimages as $oi) {
 
    
    files::add_order_file($order->orderID, array_values((array)$oi)[0]);
}
//$pharmacy_order_products->
$placeUsers=pharmacies::get_place_team($order->placeID, pharmacies::ordersperm);
$notify=new notifiaction();
$notify->orderID=$order->orderID;
$notify->details="طلب جديد ".$address;
$notify->notificationTime= time();
$notify->notifiName="طلب جديد ".$pharmacies['placeName'];
$notify->userID=$pharmacy->ownerID;
$notify->create();
foreach ($placeUsers as $placeUser){
    $notify->notifiID=0;
    $notify->userID=$placeUser['userID'];
    $notify->create();
    
}


$pharmacies['orderID']=$order->orderID;
$pharmacies['status']=TRUE;
        
echo json_encode($pharmacies);

    }
    
    static function add_order() {

    }
    
}
