<?php 

use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Kutia\Larafirebase\Facades\Larafirebase;
use Illuminate\Support\Facades\Mail;
use App\Mail\mailFastpay;




/*----------------------
  INTEGRATION CINETPAY
-----------------------*/
use App\Cinetpay\Cinetpay;
use App\Cinetpay\CinetPayService;
use App\Models\Daypay;

/**
     * ------------------------------
     *  SYSTEME DE PAIEMENT CINETPAY
     * ------------------------------
     */  
        //API key
        function apikey()
        {
          $api = '178040662466030581ef7ac4.85420856';
          return $api;
        }

        //SITE ID
        function siteID()
        {
          $site_id = '5870283';
          return $site_id;
        }

        //GUICHET DE PAIEMENT CINETPAY
        function Guichet($transaction_id,$montant,$description_trans,$client_name,$client_surname,$client_phone,$client_email,$notify_url,$return_url)
        {
          try {
            //Parameter
            $currency = 'XOF';
            $amount = $montant;
            $description = $description_trans;
            //Initiate variable for credit card
            $alternative_currency = 'XOF';
            $customer_email = $client_email;
            $customer_phone_number =$client_phone;
            $customer_address = 'Abidjan';
            $customer_city = 'Abidjan';
            $customer_country = 'CI';
            $customer_state = 'ABJ';
            $customer_zip_code ='225';
            //Transaction ID
            $id_transaction = $transaction_id;
            //apiKey
            $apikey = apikey();
            //siteId
            $site_id = siteID();
            //version
            $version = "V2";
            //notify url
            $notify_url = env('APP_URL').'api/'.$notify_url;
            //return url
            $return_url = env('APP_URL').'api/'.$return_url;
            //Channel list
            $channels = "ALL";
            //Create Guichet
            $formData = array(
              "transaction_id"=> $id_transaction,
              "amount"=> $amount,
              "currency"=> $currency,
              "customer_surname"=>$client_name,
              "customer_name"=>$client_surname,
              "description"=> $description,
              "notify_url" => $notify_url,
              "return_url" => $return_url,
              "channels" => $channels,
              //Pour afficher le paiement par carte de crédit
              "alternative_currency" => $alternative_currency,
              "customer_email" => $customer_email,
              "customer_phone_number" => $customer_phone_number,
              "customer_address" => $customer_address,
              "customer_city" => $customer_city,
              "customer_country" => strtoupper($customer_country),
              "customer_state" => $customer_state,
              "customer_zip_code" => $customer_zip_code
            );
            //Lancement de CinetPay
            $CinetPay = new CinetPay($site_id, $apikey, $version);
            $result = $CinetPay->generatePaymentLink($formData);
            //Traitement du resultat
            if ($result['code']=='201') {
              $url = $result["data"]["payment_url"];
              return response()->json(['msg'=>$url,'info'=>'1']);
            }

          } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statusCode'=>500,
                                     'status' => false,
                                     'message' => $th->getMessage()
                                    ], 500);
          }
        }

        //Get transactions paiement by transaction_id
        function getTransPay($trans_id)
        {
          $pay = DB::table('paiement')->where('transaction_id','=',$trans_id)->first();
          return $pay;
        }

        //update payement state
        function updatePayState($trans_id)
        {
          DB::table('paiement')->where('transaction_id','=',$trans_id)->update(['state'=>1]);
        }

        //GUICHET CINETPAY DE TRANSFERT D'ARGENT
        function GuichetPayOut($transfert_id,$phone,$amount,$name,$email,$type,$payment_method,$profil_id)
        {
          $CinetPayTransfert = new CinetPayService();
          $transfert = [
            'transfer_id'=> $transfert_id,
            'type'=> $type,
            'profil_id'=>$profil_id,
            'prefix'=> '225',
            'name'=> $name,
            'phone'=> $phone,
            'email'=> $email,
            'amount'=> $amount,
            "payment_method"=> $payment_method,
            'notify_url'=> 'notify_transfert',
            'country_iso'=> 'CI',
          ];
          return  $CinetPayTransfert->sendMoney($transfert);
        }

        //Payout 
        function Payout($transfert_id,$phone,$amount,$name,$email,$type,$payment_method,$profil_id)
        {
          $res = GuichetPayOut($transfert_id,$phone,$amount,$name,$email,$type,$payment_method,$profil_id);
          if ($res->code==0) {
            return response()->json(['statusCode'=>200,
                                    'status'=>false,
                                    'message'=>"Paiement effectué avec succès",
                                    'data'=> '',
                                    'error'=> '',
                                ],200);
          }
          if ($res->code==-1) {
            return response()->json(['statusCode'=>400,
                                    'status'=>false,
                                    'message'=>"Paiement refusé, veuillez ressayer plutard",
                                    'data'=> '',
                                    'error'=> '',
                                ],400);
          }
          if ($res->code==602) {
            return response()->json(['statusCode'=>400,
                                    'status'=>false,
                                    'message'=>"Paiement non actif pour le moment",
                                    'data'=> '',
                                    'error'=> '',
                                    ],200);
          }
          if ($res->code==804) {
            return response()->json(['statusCode'=>400,
                                    'status'=>false,
                                    'message'=>"Transaction echoué, le moyen de paiement choisi est indisponible",
                                    'data'=> '',
                                    'error'=> '',
                                    ],200);
          }
          return response()->json(['statusCode'=>400,
                                'status'=>false,
                                'message'=>"Une erreur s'est produite, veuillez ressayer plutard",
                                'data'=> '',
                                'error'=> '',
                            ],200);
        
        }

       //Enregistrer le paiement
       function savePay($transaction_id,$type_paiement,$montantpay,$description,$client_name,$client_surname,$client_phone,$client_email,$montant,$fee_apps,$fee_cinetpay,$domicile,$region)
       {
         $data = ['transaction_id' => $transaction_id,
                  'type_paiement'  => $type_paiement,
                  'montantpay'     => $montantpay,
                  'description'    => $description,
                  'client_name'    => $client_name,
                  'client_surname' => $client_surname,
                  'client_phone'   => $client_phone,
                  'client_email'   => $client_email,
                  'montant'        => $montant,
                  'fee_apps'       => $fee_apps,
                  'fee_cinetpay'   => $fee_cinetpay,
                  'region'         => $region,
                  'domicile'       => $domicile,
                  ];
          DB::table('paiement')->insert($data);
       }

      //Recuper un paiement
       function checkPayment($pay_id)
       {
          $pay = DB::table('paiement')->where('transaction_id','=',$pay_id)->first();
          return $pay;
       }

      function support(){
        return "support@immover.io";
      }
      function admEmail()
      {
          return "devdodo225@gmail.com";
      }
      function gymsemail1()
      {
          return "ajdrci2024@gmail.com";
      }
      function gymsemail2()
      {
          return "teknolojange@gmail.com";
      }
      //Send Email TEXT
      function SendEmail($to,$titre,$msg)
      {
          // $from = support();
          // $subject = $titre;
          // $message = $msg;
          // $headers = "From:" . $from;
          // mail($to,$subject,$message, $headers);

          $contenu = [
            'titre' => $titre,
            'msg' => $msg
          ];  
    
        Mail::to($to)->send(new mailFastpay($contenu));
    
        return response()->json([
            'statuscode' => 200,
            'status'     => true,
            'message'    => 'message envoyé avec succès'
         ], 200);
      }

      
      function convertDateFormat($format,$date)
      {
          return Carbon::parse($date)->format($format);
      }

      function paydayBetweenTwoDays($startDate,$endDate)
      {
          $startDate = convertDateFormat('d-m-Y',$startDate);
          $endDate   = convertDateFormat('d-m-Y',$endDate);
          $client = Daypay::whereBetween('datepay',[$startDate, $endDate])->get();
          $data = [];
          foreach ($client as $clt) {
            $data[] = [
              "nom" => $clt->nom,
              "prenom" => $clt->prenom,
              "tel" => $clt->tel,
              "datepay" => $clt->datepay,
              "montant" => $clt->montant,
              "frais" => $clt->frais,
              "debut" => $clt->debut,
              "fin" => $clt->fin,
              "transaction_id" => $clt->transaction_id,
            ];
          }
          return $data; 
      }

      //Generate user Password
      function generateUserPass($lettre)
      {
          do{
            $code = $lettre."-".rand(0,99999);
            $codePass =  strtoupper($code);
            $comd = DB::table('user_backs')->where('passwordfirst','=',$codePass)->first();
          } while ($comd!=null);
          return $codePass;
      }

      
    //Format price
    function formatPrice($price)
    {
      return number_format($price, 0,',', '.');
    }



