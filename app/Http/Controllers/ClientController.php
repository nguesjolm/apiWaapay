<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Daypay;
use App\Models\Inscription;
use App\Models\Monthpay;
use App\Models\PayoutAdmin;
use App\Models\PayoutUser;
use App\Models\Setting;
use App\Models\Superadminsolde;
use App\Models\Superadminsoldepay;
use App\Models\User;
use Illuminate\Http\Request;
// use Carbon\Carbon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * ----------------------------
     *   PAIEMENT CINETPAY
     * ----------------------------
     */

    //Payments days
    function dayspay(Request $request){
        $nom = $request->nom;
        $prenom = $request->prenom;
        $tel = str_replace(' ', '', $request->tel);
        $autre_montant = str_replace(' ', '', $request->autre_montant);
        $pack = "dayspay";
        $transaction_id = date("YmdHis");
        $notify_url = "notify_pay";
        $return_url = "return_pay";
        $description_trans = "Payment day";
        //Check Adhésion
        $resf = Inscription::firstWhere('tel',$tel);

        if($resf==""){
            return response()->json([
                'statuscode'=>404,
                'status'  => false,
                'message' => "Veuillez activer votre adhésion"
               ], 200);
        }else{
            //calcul du montant
            if($autre_montant){
              $montant_setting = $autre_montant;
            }else{
                $montant_setting = Setting::firstWhere('idsettings',1)->payday;
            }
           
            $fee_apps = 100;
            $montant_global = $montant_setting+$fee_apps;
            $fee_cinetpay = (3.5 * $montant_global)/100;
            $montant = intval($montant_global+$fee_cinetpay);

            $notify_url = "notify_pay";
            $return_url = "return_pay";
            $description_trans = "Payment days";
            //save transaction
            savePay($transaction_id,$pack,$montant,$description_trans,$nom,$prenom,$tel,'devdodo225@gmail.com',$montant_setting,$fee_apps,$fee_cinetpay,"","");
            //generate link payments
            $link = Guichet($transaction_id,$montant,$description_trans,$nom,$prenom,$tel,'devdodo225@gmail.com',$notify_url,$return_url); 
            //return link
            return response()->json([
                'statuscode'=>200,
                'status'  => true,
                'message' => "Payment days",
                'payment' => $link,
            ], 200);
        }

    }
    //Payments month
    function monthpay(Request $request){
        $nom = $request->nom;
        $prenom = $request->prenom;
        $tel = str_replace(' ', '',$request->tel);
        $pack = "monthpay";
        $transaction_id = date("YmdHis");
        $autre_montant = str_replace(' ', '', $request->autre_montant);

        //calcul du montant
        if($autre_montant){
            $montant_setting = $autre_montant;
        }else{
              $montant_setting = Setting::firstWhere('idsettings',1)->monthpay;
        }
        //calcul du montant
        $montant_setting = Setting::firstWhere('idsettings',1)->monthpay;
        $fee_apps = 200;
        $montant_global = $montant_setting+$fee_apps;
        $fee_cinetpay = (3.5 * $montant_global)/100;
        $montant = intval($montant_global+$fee_cinetpay);

        $notify_url = "notify_pay";
        $return_url = "return_pay";
        $description_trans = "Payment month";
        //save transaction
        savePay($transaction_id,$pack,$montant,$description_trans,$nom,$prenom,$tel,'devdodo225@gmail.com',$montant_setting,$fee_apps,$fee_cinetpay,"","");
        //generate link payment
        $link = Guichet($transaction_id,$montant,$description_trans,$nom,$prenom,$tel,'devdodo225@gmail.com',$notify_url,$return_url);
        //return link
        return response()->json([
            'statuscode'=>200,
            'status'  => true,
            'message' => "Payment months",
            'payment' => $link,
           ], 200);
    }
    //Payments inscription
    function inscriptionPay(Request $request){
        $nom = $request->nom;
        $prenom = $request->prenom;
        $domicile = $request->domicile;
        $region = $request->region;
        $tel = str_replace(' ', '',$request->tel);
        $pack = "inscriptionpay";
        $transaction_id = date("YmdHis");
        
        //calcul du montant
        $autre_montant = str_replace(' ', '', $request->autre_montant);
        if($autre_montant){
            $montant_setting = $autre_montant;
        }else{
              $montant_setting = Setting::firstWhere('idsettings',1)->inscriptionpay;
        }

        $fee_apps = 100;
        $montant_global = $montant_setting+$fee_apps;
        $fee_cinetpay = (3.5 * $montant_global)/100;
        $montant =intval($montant_global+$fee_cinetpay);
        $notify_url = "notify_pay";
        $return_url = "return_pay";
        $description_trans = "Payment incription";
        //save transaction
        savePay($transaction_id,$pack,$montant,$description_trans,$nom,$prenom,$tel,'devdodo225@gmail.com',$montant_setting,$fee_apps,$fee_cinetpay,$domicile,$region);
        //generate link payment
        $link = Guichet($transaction_id,$montant,$description_trans,$nom,$prenom,$tel,'devdodo225@gmail.com',$notify_url,$return_url);
        //return link payment
        return response()->json([
            'statuscode'=>200,
            'status'  => true,
            'message' => "Payment inscription",
            'payment' => $link,
           ], 200);
    }

    //notify
    public function notify_pay(Request $request)
    {
        //Id transaction
        $id_transaction = $request->cpm_trans_id; 	
        //apiKey
        $apikey = apikey();
        //Veuillez entrer votre siteId
        $site_id = siteID();
        //Version
        $version = "V2";
        //Verification du paiement
        $pay = checkPayment($id_transaction);

        if($pay){
            if($pay->state!=1){
                //Nouveau paiement
                $curl = curl_init();
                curl_setopt_array($curl, array(
                     CURLOPT_URL => 'https://api-checkout.cinetpay.com/v2/payment/check',
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_ENCODING => '',
                     CURLOPT_MAXREDIRS => 10,
                     CURLOPT_TIMEOUT => 0,
                     CURLOPT_FOLLOWLOCATION => true,
                     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                     #curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0),
                     #curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0),
                     CURLOPT_CUSTOMREQUEST => 'POST',
                     CURLOPT_POSTFIELDS =>     '{
                         "transaction_id":"'.$id_transaction.'",
                         "site_id": "'.$site_id.'",
                         "apikey" : "'.$apikey.'"
                     }',
                     CURLOPT_HTTPHEADER => array(
                         'Content-Type: application/json'
                     ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                $result = json_decode($response);

                if($result->{'code'}=='00'){
                    #Changer le statut de paiement :: en 1 pour paiement succès
                    $payment = getTransPay($id_transaction);
                    #Changer le statut de paiement :: en 1 pour paiement succès
                    updatePayState($id_transaction);
                    $date_cmd =  date('Y-m-d');
                    $date_debut = Carbon::parse($date_cmd);
                     # Enregistrement de la commande
                    if($payment->type_paiement=='monthpay'){
                        $monthpay = Monthpay::firstOrCreate([
                            'nom'     => $payment->client_name,
                            'prenom'  => $payment->client_surname,
                            'tel'     => $payment->client_phone,
                            'datepay' => date('d-m-Y'),
                            'montant' => $payment->montant,
                            'frais'   => $payment->fee_apps+$payment->fee_cinetpay,
                            'debut'   => date('d-m-Y'),
                            'fin'     => convertDateFormat('d-m-Y',$date_debut->addDays(30)),
                            'transaction_id' => $payment->transaction_id,
                        ]);
                        # Créditer le solde du compte Admin
                        $res = Superadminsoldepay::firstWhere('transaction_id',$payment->transaction_id);
                        if(!$res){
                          Superadminsoldepay::create([
                              'typepay'       => $payment->type_paiement,
                              'montant'       => $payment->montant,
                              'frais_admin'   => $payment->fee_apps,
                              'frais_cinetpay'=> $payment->fee_cinetpay,
                              'nom_client'    => $payment->client_name,
                              'tel_client'    => $payment->client_phone,
                              'transaction_id' => $payment->transaction_id,
                          ]);
                          $solde = Superadminsolde::firstWhere('idsuperAdmin',1)->solde+$payment->fee_apps;
                          Superadminsolde::where('idsuperAdmin',1)->update(['solde'=>$solde]);
                          #send email to admin
                          SendEmail(admEmail(),"AJRDCI-Paiement de Kits",'solde:'.$solde);

                           #Crediter solde gyms
                            $soldegyms = Superadminsolde::firstWhere('idsuperAdmin',1)->soldeGyms+$payment->montant;
                            Superadminsolde::where('idsuperAdmin',1)->update(['soldeGyms'=>$soldegyms]);
                            SendEmail(gymsemail1(),$payment->client_name." Paiement de Kits",'solde:'.$soldegyms);
                            // SendEmail(gymsemail1(),$payment->type_paiement,'solde:'.$soldegyms);
                        }
                    }

                    if($payment->type_paiement=='dayspay'){
                        Daypay::firstOrCreate([
                            'nom'     => $payment->client_name,
                            'prenom'  => $payment->client_surname,
                            'tel'     => $payment->client_phone,
                            'datepay' => date('d-m-Y'),
                            'montant' => $payment->montant,
                            'frais'   => $payment->fee_apps+$payment->fee_cinetpay,
                            'debut'   => date('d-m-Y'),
                            'fin'     => date('d-m-Y'),
                            'transaction_id' => $payment->transaction_id,
                        ]);
                         # Créditer le solde du compte Admin
                         $res = Superadminsoldepay::firstWhere('transaction_id',$payment->transaction_id);
                         if(!$res){
                              Superadminsoldepay::create([
                                  'typepay'       => $payment->type_paiement,
                                  'montant'       => $payment->montant,
                                  'frais_admin'   => $payment->fee_apps,
                                  'frais_cinetpay'=> $payment->fee_cinetpay,
                                  'nom_client'    => $payment->client_name,
                                  'tel_client'    => $payment->client_phone,
                                  'transaction_id' => $payment->transaction_id,
                              ]);
                              $solde = Superadminsolde::firstWhere('idsuperAdmin',1)->solde+$payment->fee_apps;
                              Superadminsolde::where('idsuperAdmin',1)->update(['solde'=>$solde]);
                              #send email to admin
                              SendEmail(admEmail(),"AJDRCI- Paiement mensuel",'solde:'.$solde);

                               #Crediter solde gyms
                                $soldegyms = Superadminsolde::firstWhere('idsuperAdmin',1)->soldeGyms+$payment->montant;
                                Superadminsolde::where('idsuperAdmin',1)->update(['soldeGyms'=>$soldegyms]);
                                // SendEmail(gymsemail2(),$payment->type_paiement,'solde:'.$soldegyms);
                                SendEmail(gymsemail1(),$payment->client_name." Cotisation mensuelle",'solde:'.$soldegyms);
                          }
                    }

                    if($payment->type_paiement=='inscriptionpay'){
                        //Inscription adérents :: clients
                        Client::firstOrCreate([
                            'nom'     => $payment->client_name,
                            'prenom'  => $payment->client_surname,
                            'tel'     => $payment->client_phone,
                            'domicile'=> $payment->domicile,
                            'region'  => $payment->region,
                            'datemembre' => date('d-m-Y'),
                        ]);
                        Inscription::firstOrCreate([
                            'nom'     => $payment->client_name,
                            'prenom'  => $payment->client_surname,
                            'tel'     => $payment->client_phone,
                            'datepay' => date('d-m-Y'),
                            'montant' => $payment->montant,
                            'frais'   => $payment->fee_apps+$payment->fee_cinetpay,
                            'debut'   => $date_debut,
                            'fin'     => convertDateFormat('d-m-Y',$date_debut->addDays(180)),
                            'transaction_id' => $payment->transaction_id,
                        ]); 
                        # Créditer le solde du compte Admin
                        $res = Superadminsoldepay::firstWhere('transaction_id',$payment->transaction_id);
                        if(!$res){
                             Superadminsoldepay::create([
                                 'typepay'       => $payment->type_paiement,
                                 'montant'       => $payment->montant,
                                 'frais_admin'   => $payment->fee_apps,
                                 'frais_cinetpay'=> $payment->fee_cinetpay,
                                 'nom_client'    => $payment->client_name,
                                 'tel_client'    => $payment->client_phone,
                                 'transaction_id' => $payment->transaction_id,
                             ]);
                             $solde = Superadminsolde::firstWhere('idsuperAdmin',1)->solde+$payment->fee_apps;
                             Superadminsolde::where('idsuperAdmin',1)->update(['solde'=>$solde]);
                             #send email to admin
                             SendEmail(admEmail(),"AJDRCI - Paiement Adhésion",'solde:'.$solde);

                              #Crediter solde gyms
                                $soldegyms = Superadminsolde::firstWhere('idsuperAdmin',1)->soldeGyms+$payment->montant;
                                Superadminsolde::where('idsuperAdmin',1)->update(['soldeGyms'=>$soldegyms]);
                                // SendEmail(gymsemail2(),"Paiement",'solde:'.$soldegyms);
                                SendEmail(gymsemail1(),$payment->client_name." Paiement Adhésion",'solde:'.$soldegyms);
                        }
                    }
                }else{
                    return response()->json(['statusCode'=>'404',
                                                'status'=>false,
                                                'message'=>"Paiement echoué",
                                                'data'=> '',
                                                'error'=> '',
                                            ],404);
                }
            }else{
                #Changer le statut de paiement :: en 1 pour paiement succès
                $payment = getTransPay($id_transaction);
                #Changer le statut de paiement :: en 1 pour paiement succès
                updatePayState($id_transaction);
                $date_cmd =  date('Y-m-d');
                $date_debut = Carbon::parse($date_cmd);
                # Enregistrement de la commande
                if($payment->type_paiement=='monthpay'){
                  $monthpay = Monthpay::firstOrCreate([
                      'nom'     => $payment->client_name,
                      'prenom'  => $payment->client_surname,
                      'tel'     => $payment->client_phone,
                      'datepay' => date('d-m-Y'),
                      'montant' => $payment->montant,
                      'frais'   => $payment->fee_apps+$payment->fee_cinetpay,
                      'debut'   => date('d-m-Y'),
                      'fin'     => convertDateFormat('d-m-Y',$date_debut->addDays(30)),
                      'transaction_id' => $payment->transaction_id,
                  ]);
                  # Créditer le solde du compte Admin
                  $res = Superadminsoldepay::firstWhere('transaction_id',$payment->transaction_id);
                    if(!$res){
                        Superadminsoldepay::create([
                            'typepay'       => $payment->type_paiement,
                            'montant'       => $payment->montant,
                            'frais_admin'   => $payment->fee_apps,
                            'frais_cinetpay'=> $payment->fee_cinetpay,
                            'nom_client'    => $payment->client_name,
                            'tel_client'    => $payment->client_phone,
                            'transaction_id' => $payment->transaction_id,
                        ]);
                        $solde = Superadminsolde::firstWhere('idsuperAdmin',1)->solde+$payment->fee_apps;
                        Superadminsolde::where('idsuperAdmin',1)->update(['solde'=>$solde]);
                        #send email to admin
                        SendEmail(admEmail(),"AJDRCI- Paiement de Kits",'solde:'.$solde);

                        #Crediter solde gyms
                        $soldegyms = Superadminsolde::firstWhere('idsuperAdmin',1)->soldeGyms+$payment->montant;
                        Superadminsolde::where('idsuperAdmin',1)->update(['soldeGyms'=>$soldegyms]);
                        SendEmail(gymsemail1(),$payment->client_name." Paiement de Kits",'solde:'.$soldegyms);
                    }
                 
                }

                if($payment->type_paiement=='dayspay'){
                  $daypay = Daypay::firstOrCreate([
                      'nom'     => $payment->client_name,
                      'prenom'  => $payment->client_surname,
                      'tel'     => $payment->client_phone,
                      'datepay' => date('d-m-Y'),
                      'montant' => $payment->montant,
                      'frais'   => $payment->fee_apps+$payment->fee_cinetpay,
                      'debut'   => date('d-m-Y'),
                      'fin'     => date('d-m-Y'),
                      'transaction_id' => $payment->transaction_id,
                  ]);
                   # Créditer le solde du compte Admin
                   $res = Superadminsoldepay::firstWhere('transaction_id',$payment->transaction_id);
                   if(!$res){
                        Superadminsoldepay::create([
                            'typepay'       => $payment->type_paiement,
                            'montant'       => $payment->montant,
                            'frais_admin'   => $payment->fee_apps,
                            'frais_cinetpay'=> $payment->fee_cinetpay,
                            'nom_client'    => $payment->client_name,
                            'tel_client'    => $payment->client_phone,
                            'transaction_id' => $payment->transaction_id,
                        ]);
                        $solde = Superadminsolde::firstWhere('idsuperAdmin',1)->solde+$payment->fee_apps;
                        Superadminsolde::where('idsuperAdmin',1)->update(['solde'=>$solde]);
                        #send email to admin
                        SendEmail(admEmail(),"AJDRCI- Paiement mensuel",'solde:'.$solde);

                         #Crediter solde gyms
                         $soldegyms = Superadminsolde::firstWhere('idsuperAdmin',1)->soldeGyms+$payment->montant;
                         Superadminsolde::where('idsuperAdmin',1)->update(['soldeGyms'=>$soldegyms]);
                         // SendEmail(gymsemail2(),$payment->type_paiement,'solde:'.$soldegyms);
                         SendEmail(gymsemail1(),$payment->client_name." Cotisation mensuelle",'solde:'.$soldegyms);
                    }
                }

                if($payment->type_paiement=='inscriptionpay'){
                     //Inscription adérents :: clients
                        Client::firstOrCreate([
                            'nom'     => $payment->client_name,
                            'prenom'  => $payment->client_surname,
                            'tel'     => $payment->client_phone,
                            'domicile'=> $payment->domicile,
                            'region'  => $payment->region,
                            'datemembre' => date('d-m-Y'),
                        ]);
                    $inscriptionPay = Inscription::create([
                        'nom'     => $payment->client_name,
                        'prenom'  => $payment->client_surname,
                        'tel'     => $payment->client_phone,
                        'datepay' => date('d-m-Y'),
                        'montant' => $payment->montant,
                        'frais'   => $payment->fee_apps+$payment->fee_cinetpay,
                        'debut'   => date('d-m-Y'),
                        'fin'     => convertDateFormat('d-m-Y',$date_debut->addDays(180)),
                        'transaction_id' => $payment->transaction_id,
                    ]); 
                    # Créditer le solde du compte Admin
                    $res = Superadminsoldepay::firstWhere('transaction_id',$payment->transaction_id);
                    if(!$res)
                    {
                        Superadminsoldepay::create([
                            'typepay'       => $payment->type_paiement,
                            'montant'       => $payment->montant,
                            'frais_admin'   => $payment->fee_apps,
                            'frais_cinetpay'=> $payment->fee_cinetpay,
                            'nom_client'    => $payment->client_name,
                            'tel_client'    => $payment->client_phone,
                            'transaction_id' => $payment->transaction_id,
                        ]);
                        $solde = Superadminsolde::firstWhere('idsuperAdmin',1)->solde+$payment->fee_apps;
                        Superadminsolde::where('idsuperAdmin',1)->update(['solde'=>$solde]);
                        #send email to admin
                         SendEmail(admEmail(),"AJDRCI - Paiement Adhésion",'solde:'.$solde);

                        #Crediter solde gyms
                        $soldegyms = Superadminsolde::firstWhere('idsuperAdmin',1)->soldeGyms+$payment->montant;
                        Superadminsolde::where('idsuperAdmin',1)->update(['soldeGyms'=>$soldegyms]);
                        // SendEmail(gymsemail2(),$payment->type_paiement,'solde:'.$soldegyms);
                        SendEmail(gymsemail1(),$payment->client_name." Paiement Adhésion",'solde:'.$soldegyms);
                    }
                }

            }
        }
    }
    //returnpay :: PAY IN
    function return_pay(Request $request)
    {
        $id_transaction = $request->transaction_id;
        #Verification du paiement
        $payment = getTransPay($id_transaction);
        $date_cmd =  date('Y-m-d');
        $date_debut = Carbon::parse($date_cmd);
        $date_debut->addDays(30);

        if ($payment->type_paiement=='monthpay') {
            $msg = "paiement effectué avec succès, votre abonnement mensuel est actvé et prendra effet du ".convertDateFormat('d-m-Y',$date_cmd)." au ".convertDateFormat('d-m-Y',$date_debut->addDays(30));
        }

        if ($payment->type_paiement=='monthpay') {
            $msg = "paiement effectué avec succès, votre abonnement prestige est actvé et prendra effet du ".convertDateFormat('d-m-Y',$date_cmd)." au ".convertDateFormat('d-m-Y',$date_debut->addDays(30));
        }

        if ($payment->type_paiement=='dayspay') {
            $msg = "paiement effectué avec succès, votre forfait est actvé pour la journée du ".$date_cmd;
        }

        if($payment->state==1){
            // return response()->json(['statusCode'=>'200',
            //                          'status'=>true,
            //                         'message'=> $msg,
            //                             'data'=> '',
            //                             'error'=> '',
            //                         ],200);
            // Redirection vers une URL spécifique
            // header("Location:https://immover.io/"); 
            // exit;
            return redirect('https://ajrdci.immover.io/successpay');
        }else{
            // return response()->json(['statusCode'=>'404',
            //                          'status'=>false,
            //                          'message'=>"Paiement echoué",
            //                          'data'=> '',
            //                          'error'=> '',
            //                         ],404);
            // header("Location:https://immover.io/"); https://ajrdci.immover.io/failedpay
            // exit;

            // return redirect('https://immover.io/');
            return redirect('https://ajrdci.immover.io/failedpay');
        }
    }

    //payout gyms
    function payout_gyms(Request $request)
    {
        try {
            //Validated
            $validate_livreur = Validator::make($request->all(),[
                'montant'    => 'required'
            ]);
            if ($validate_livreur->fails()) 
            {
                return response()->json(['statusCode'=>'404',
                                         'status'=>'false',
                                         'message'=>'Erreur de validation',
                                         'data'=> '',
                                         'error'=> $validate_livreur->errors(),
                                        ]);
            }
            #check user
            $user = User::firstWhere('id', $request->user_id);
            #Préparation du Guichet de paiement
            $transfert_id = date("YmdHis");
            $phone = $user->tel;
            $name  = $user->name;
            $email = $user->email;
            $type = 'user gyms';
            $profil_id = $request->user_id;
            $montant = $request->montant;
            $payment_method = $request->payment_method;
            $phone_money = $request->phone_money;
            #Lancement du guichet de paiement
            if(Superadminsolde::firstWhere('idsuperAdmin',1)->soldeGyms >= $montant)
            {
               $res = GuichetPayOut($transfert_id,$phone_money,$montant,$name,$email,$type,$payment_method,$profil_id);
               return $res;
               if($res){
                    if ($res->code==0) {
                            PayoutUser::firstOrCreate([
                                'nom'       => $name,
                                'prenom'    => $name,
                                'tel'       => $phone_money,
                                'montant'   => $montant,
                                'trans_id'  => $transfert_id
                            ]);
                            $soldegyms = Superadminsolde::firstWhere('idsuperAdmin',1)->soldeGyms-$montant;
                            Superadminsolde::where('idsuperAdmin',1)->update(['soldeGyms'=>$soldegyms]);
                            #solde gyms mise à jour
                            return response()->json(['statusCode'=>200,
                                                    'status'=>true,
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
                                                ],400);
                    }
                    if ($res->code==804) {
                        return response()->json(['statusCode'=>400,
                                                'status'=>false,
                                                'message'=>"Transaction echoué, le moyen de paiement choisi est indisponible",
                                                'data'=> '',
                                                'error'=> '',
                                                ],400);
                    }
               }else{
                return $res;
               }




            }else{
                return response()->json(['statusCode'=>404,
                                            'status'=>false,
                                            'message'=>"Votre solde est insuffisant",
                                            'data'=> '',
                                            'error'=> '',
                                        ],404);
            }



        } catch (\Throwable $th) {
            //throw $th;
            //throw $th;
            return response()->json([
                'statusCode'=>500,
                'status' => false,
                'message' => $th->getMessage(),
                
            ], 500);
        }
    }

    //payout admin
    function payout_admin(Request $request)
    {
        try {
            //Validated
            $validate_livreur = Validator::make($request->all(),[
                'montant'    => 'required'
            ]);
            if ($validate_livreur->fails()) 
            {
                return response()->json(['statusCode'=>'404',
                                         'status'=>'false',
                                         'message'=>'Erreur de validation',
                                         'data'=> '',
                                         'error'=> $validate_livreur->errors(),
                                        ]);
            }
            #check user
            $user = User::firstWhere('id', $request->user_id);
            #Préparation du Guichet de paiement
            $transfert_id = date("YmdHis");
            $phone = $user->tel;
            $name  = $user->name;
            $email = $user->email;
            $type = 'user admin';
            $profil_id = $request->user_id;
            $montant = $request->montant;
            $payment_method = $request->payment_method;
            $phone_money = $request->phone_money;
            #Lancement du guichet de paiement
            if(Superadminsolde::firstWhere('idsuperAdmin',1)->solde >= $montant)
            {
               $res = GuichetPayOut($transfert_id,$phone_money,$montant,$name,$email,$type,$payment_method,$profil_id);
               return $res;
               if($res){
                    if ($res->code==0) {
                            PayoutAdmin::firstOrCreate([
                                'nom'       => $name,
                                'prenom'    => $name,
                                'tel'       => $phone_money,
                                'montant'   => $montant,
                                'trans_id'  => $transfert_id
                            ]);
                            $solde = Superadminsolde::firstWhere('idsuperAdmin',1)->solde-$montant;
                            Superadminsolde::where('idsuperAdmin',1)->update(['solde'=>$solde]);
                            #solde gyms mise à jour
                            return response()->json(['statusCode'=>200,
                                                    'status'=>true,
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
                                                ],400);
                    }
                    if ($res->code==804) {
                        return response()->json(['statusCode'=>400,
                                                'status'=>false,
                                                'message'=>"Transaction echoué, le moyen de paiement choisi est indisponible",
                                                'data'=> '',
                                                'error'=> '',
                                                ],400);
                    }
               }else{
                return $res;
               }




            }else{
                return response()->json(['statusCode'=>404,
                                            'status'=>false,
                                            'message'=>"Votre solde est insuffisant",
                                            'data'=> '',
                                            'error'=> '',
                                        ],404);
            }



        } catch (\Throwable $th) {
            //throw $th;
            //throw $th;
            return response()->json([
                'statusCode'=>500,
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    
    /**
     * ----------------------------
     *   HISTORIQUE DE PAIEMENT
     * ----------------------------
     */

    //check payment
    function checkclientPay(Request $request){
        $tel = str_replace(' ', '',$request->tel);
        return $tel;
        //check client phone
        //genrate code OTP
    }

    //story
    function storyPay(Request $request)
    {
        //check OTP
        $code = $request->code_otp;
        return $code;
        //get story
    }
}
