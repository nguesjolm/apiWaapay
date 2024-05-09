<?php

namespace App\Http\Controllers;

use App\Models\About;
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
use App\Models\UserBack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
       * ------------------
       * SETTING PAYEMENT
       * -----------------
        */
        public function paysetting(Request $request)
        {
            $payday = $request->payday;
            $monthpay = $request->monthpay;
            $inscriptionpay = $request->inscriptionpay;
  
            if($payday)
            {
                Setting::where('idsettings',1)->update(['payday'=>$payday]);
            }
            if($monthpay)
            {
                Setting::where('idsettings',1)->update(['monthpay'=>$monthpay]);
            }
            if($inscriptionpay)
            {
                Setting::where('idsettings',1)->update(['inscriptionpay'=>$inscriptionpay]);
            }

            return response()->json([
                'statusCode'=>200,
                'status' => true,
                'message' => "Mise à jour effectuée avec succès",
            ], 200);
        }
         //get settings pay
         public function getpaysetting(Request $request)
         {
             return Setting::all();
         }
 
    
    
    /**
       * ---------------
       * PAYMENT STORY
       * ---------------
       */
       //Day payment story
       public function daypaystory(){
            $payday_data =  Daypay::all();
            $journalier_solde = 0;
            foreach($payday_data as $pay_item)
            {
                $journalier_solde = $journalier_solde+$pay_item->montant;
            }
            return response()->json([
                'statusCode'=>200,
                'status' => true,
                'pay_data' => $payday_data,
                'pay_solde' => $journalier_solde,
                'message' => "liste des paiements",
            ], 200);
       }
       //Month payment story
       public function monthpaystory()
       {
            // return Monthpay::all();
            $pay_data =  Monthpay::all();
            $pay_solde = 0;
            foreach($pay_data as $pay_item)
            {
                $pay_solde = $pay_solde+$pay_item->montant;
            }
            return response()->json([
                'statusCode'=>200,
                'status' => true,
                'pay_data' => $pay_data,
                'pay_solde' => $pay_solde,
                'message' => "liste des paiements",
            ], 200);
       }
       //Inscription payment story
       public function inscriptionpaystory(){
            // return Inscription::all();
            $pay_data =  Inscription::all();
            $pay_solde = 0;
            foreach($pay_data as $pay_item)
            {
                $pay_solde = $pay_solde+$pay_item->montant;
            }
            return response()->json([
                'statusCode'=>200,
                'status' => true,
                'pay_data' => $pay_data,
                'pay_solde' => $pay_solde,
                'message' => "liste des paiements",
            ], 200);
       }
        //story payement between two date by payement type
        public function paystat(Request $request){
            $start = $request->start;
            $end = $request->end;
            $payment_type = $request->type_pay;
            #client connectés
            $startDate = convertDateFormat('d-m-Y',$start);
            $endDate   = convertDateFormat('d-m-Y',$end);
            #day payment
            $payment_day = paydayBetweenTwoDays($startDate,$endDate);
            $nb_payment_day = count(Daypay::whereBetween('datepay',[$startDate, $endDate])->get());
            dd($nb_payment_day);
            #pro connectés
            // return response()->json([
            //     'statusCode'=>200,
            //     'status'    => true,
            //     'data_pay'  => $payment_day,
            //     'nb_pay'    => $nb_payment_day,
            //     'message'   => "Mise à jour effectuée avec succès",
            // ], 200);
        }
       
    /**
       * ---------------
       * PAYMENT OUT
       * ---------------
       */
        //payout user
        public function payoutuser(Request $request)
        {
            $montant = $request->montant;
            $payment_method = $request->payment_method;
            $user_id = $request->user_id;
            $payment_phone = $request->payment_phone;
            //tel
            $validatetel = Validator::make($request->All(),[
                'payment_phone'=> 'required',
            ]);
            if ($validatetel->fails()) {
                //en case d'erreur ou serveur n'est pas disponible
                return response()->json(['statuscode'=>'406',
                                        'status'=>'false',
                                        'message'=>'veuillez préciser le numéro mobile money',
                                        'data'=> '',
                                        'error'=> $validatetel->errors(),
                                        ],406);
            }

            //check user
            if($user_id)
            {
                $user = User::firstWhere('id',$user_id);
                if($user)
                {
                 //Preparation du guichet
                 $transfert_id = date("YmdHis");
                 $nom = $user->name;
                 $prenom = $user->name;
                 $email = $user->email;
                 $tel = $user->tel;
                 $datepay = date('d-m-Y');
                 $heure = date('H:m');
                 $type = "user pay";
                 $profil_id = $user_id;
                 //Teste
                //  PayoutUser::firstOrCreate([
                //     'nom'=>$nom,
                //     'prenom' => $prenom,
                //     'tel' => $tel,
                //     'phone_money' => $payment_phone,
                //     'montant' => $montant,
                //     'datepay'=>$datepay,
                //     'heure'=> $heure,
                //     'trans_id'=>$transfert_id
                //  ]);
                //  $solde_user = Superadminsolde::firstWhere('idsuperAdmin',1)->soldeGyms;
                //  $new_solde = $solde_user - $montant;
                //  Superadminsolde::where('idsuperAdmin',1)->update(['soldeGyms'=>$new_solde]);

                 //Lancement de CinetPay pour le transfert
                 $res = Payout($transfert_id,$payment_phone,$montant,$nom,$email,$type,$payment_method,$profil_id);
                 //check state payment
                 if($res['statusCode']==200){
                        //save transaction
                        PayoutUser::firstOrCreate([
                            'nom'=>$nom,
                            'prenom' => $prenom,
                            'tel' => $tel,
                            'montant' => $montant,
                            'datepay'=>$datepay,
                            'heure'=> $heure,
                            'trans_id'=>$transfert_id
                        ]);
                        //Mise à jour du solde Pay::firstWhere('id',$value['pays_id'])->nom,
                        $solde_user = Superadminsolde::firstWhere('idsuperAdmin',1)->soldeGyms;
                        $new_solde = $solde_user - $montant;
                        Superadminsolde::where('idsuperAdmin',1)->update(['soldeGyms'=>$new_solde]);

                    return $res;
                 }else{
                    return $res;
                 }
                }
               

            }
           
        }
        //payout user story
        public function payoutuserStory(Request $request)
        {
            
            
            return response()->json(['statuscode'=>'200',
                                     'status'    =>true,
                                     'message'  =>'historique des paiment sortants',
                                     'pay_data' => PayoutUser::all(),
                                     'solde_pay' => Superadminsolde::firstWhere('idsuperAdmin','1')->soldeGyms
                                    ],200);
        }
    
    /**
       * ---------------
       * PAYMENT OUT
       * ---------------
       */ 
        //solde stat
        public function solde_stat(Request $request)
        {
          return Superadminsolde::all();
        } 
        //fastpay in story
        public function fastpayinstory(Request $request)
        {   
            // return Superadminsoldepay::all();
            return response()->json(['statuscode'=>'200',
                                     'status'=>true,
                                     'message'=>'historique des paiements',
                                     'payin'=> Superadminsoldepay::all(),
                                     'solde'=>Superadminsolde::firstWhere('idsuperAdmin','1')->solde
                                    ],200);
        }
        //fastpay out story
        //fastpay out
        public function fastpayout(Request $request)
        {
            $montant = $request->montant;
            $payment_method = $request->payment_method;
            $user_id = $request->user_id;
            $payment_phone = $request->payment_phone;
            //tel
            $validatetel = Validator::make($request->All(),[
                'payment_phone'=> 'required',
            ]);
            if ($validatetel->fails()) {
                //en case d'erreur ou serveur n'est pas disponible
                return response()->json(['statuscode'=>'406',
                                        'status'=>'false',
                                        'message'=>'veuillez préciser le numéro mobile money',
                                        'data'=> '',
                                        'error'=> $validatetel->errors(),
                                        ],406);
            }

            //check user
            if($user_id)
            {
                $user = User::firstWhere('id',$user_id);
                if($user)
                {
                 //Preparation du guichet
                 $transfert_id = date("YmdHis");
                 $nom = $user->name;
                 $prenom = $user->name;
                 $email = $user->email;
                 $tel = $user->tel;
                 $datepay = date('d-m-Y');
                 $heure = date('H:m');
                 $type = "user pay";
                 $profil_id = $user_id;
                 //Teste
                 PayoutAdmin::firstOrCreate([
                    'nom'=>$nom,
                    'prenom' => $prenom,
                    'tel' => $tel,
                    'phone_money' => $payment_phone,
                    'montant' => $montant,
                    'datepay'=>$datepay,
                    'heure'=> $heure,
                    'trans_id'=>$transfert_id
                 ]);

                 //Lancement de CinetPay pour le transfert
                 $res = Payout($transfert_id,$payment_phone,$montant,$nom,$email,$type,$payment_method,$profil_id);
                 //check state payment
                 if($res['statusCode']==200){
                        //save transaction
                        PayoutUser::firstOrCreate([
                            'nom'=>$nom,
                            'prenom' => $prenom,
                            'tel' => $tel,
                            'montant' => $montant,
                            'datepay'=>$datepay,
                            'heure'=> $heure,
                            'trans_id'=>$transfert_id
                        ]);
                        //Mise à jour du solde
                        $solde_user = Superadminsolde::firstWhere('idsuperAdmin',1)->solde;
                        $new_solde = $solde_user - $montant;
                        Superadminsolde::where('idsuperAdmin',1)->update(['solde'=>$new_solde]);
                        return $res;
                 }else{
                    return $res;
                 }
                }
               

            }
        }

    /**
       * ---------------
       * STAT GLOBALE
       * ---------------
       */
        //stats global
        public function statsglobal(Request $request)
        {
            //total payement 1 :: journalier
            $payday_data = Daypay::all();
            $payday_data_last = Daypay::latest()->take(10)->get();
            $journalier_solde = 0;
            foreach($payday_data as $pay_item)
            {
                $journalier_solde = $journalier_solde+$pay_item->montant;
            }
            //total payment 2 :: adhésion
            $payinscrip_data = Inscription::all();
            $payinscrip_data_last = Inscription::latest()->take(10)->get();
            $adhesion_solde = 0;
            foreach($payinscrip_data as $pay_item)
            {
                $adhesion_solde = $adhesion_solde+$pay_item->montant;
            }
            //total payement 3 :: forfait autre
            $forfait_data = Monthpay::all();
            $monthpay_data_last = Monthpay::latest()->take(10)->get();
            $monthpay_solde = 0;
            foreach($forfait_data as $pay_item)
            {
                $monthpay_solde = $monthpay_solde+$pay_item->montant;
            }
            //count client
             $client_nb = count(Client::all());
             $client_data_last = Client::latest()->take(10)->get();

            //result
            return response()->json(['statuscode'=>200,
                                     'status'=>true,
                                     'message'=>'statistique globale',
                                     'journalier_solde'=> formatPrice($journalier_solde),
                                     'journalier_data_last' => $payday_data_last,
                                     'adhesion_solde'=> formatPrice($adhesion_solde),
                                     'adhesion_data_last'=> $payinscrip_data_last,
                                     'monthpay_data_last'=> $monthpay_data_last,
                                     'monthpay_data_solde'=> formatprice($monthpay_solde),
                                     'client_nb' => $client_nb,
                                     'client_data_last' => $client_data_last,
                                    ],200);

        }

    /**
     * -----------------
     *  USER COMPTE
     * -----------------
     */
       //create user count
       public function createuserCompte(Request $request){
            //nom
            $validatenom = Validator::make($request->All(),[
                'nom'=> 'required',
            ]);
            if ($validatenom->fails()) {
                //en case d'erreur ou serveur n'est pas disponible
                return response()->json(['statuscode'=>'406',
                                        'status'=>false,
                                        'message'=>'Veuillez préciser le nom',
                                        'data'=> '',
                                        'error'=> $validatenom->errors(),
                                        ],200);
            }

            //email
            $validateemail = Validator::make($request->All(),[
                'email'=> 'required | email | unique:users',
            ]);
            if ($validateemail->fails()) {
                //en case d'erreur ou serveur n'est pas disponible
                return response()->json(['statuscode'=>'406',
                                        'status'=>false,
                                        'message'=>'Veuillez préciser le email ou cet email est déjà utilisé',
                                        'data'=> '',
                                        'error'=> $validateemail->errors(),
                                        ],200);
            }

            //tel
            $validatetel = Validator::make($request->All(),[
                'tel'=> 'required | unique:users',
            ]);
            if ($validatetel->fails()) {
                //en case d'erreur ou serveur n'est pas disponible
                return response()->json(['statuscode'=>'406',
                                        'status'=>'false',
                                        'message'=>'ce numéro est déjà utilisé ou veuillez vérifier',
                                        'data'=> '',
                                        'error'=> $validatetel->errors(),
                                        ],200);
            }

           // Create user count and générate password to send on email
            $password = generateUserPass("waapay");
            $user = User::create([
                'name' => $request->nom,
                'email' => $request->email,
                'tel' => $request->tel,
                'datecreate'  => date('d-m-Y'),
                'password'=>  Hash::make($password)
            ]);
            $msg = "email: ".$request->email." Mot de passe: ".$password;
            SendEmail($request->email,"Accès Back-office",$msg);
            // #Save user role
            UserBack::create([
                "payment"  => $request->payment,
                "parametres"  => $request->parametre,
                "solde"  => $request->solde,
                "user_id"  => $user->id,
                "passwordfirst" => $password,
            ]);
            return response()->json(['statuscode'=>200,
                                    'status'=>true,
                                    'message'=>'Compte utilisateur ouvert avec succès',
                                    'error'=> "",
                                ],200);
       }
       //user story
       public function userstory(Request $request)
       {
         return User::all();
       }
       //client story
       public function clientstory(Request $request)
       {
         return Client::all();
       }
       //login user
       public function loginfastpay(Request $request)
       {
         $email = $request->email;
         $password = $request->password;
         
         if (!Auth::attempt($request->only(['email','password']))) 
         {
                 return response()->json([
                     'statuscode'=>404,
                     'status' => false,
                     'message' => "Les coordonnées de connection ne correspondent pas à nos enregistrement",
                 ], 404);
         }
         $user = User::where('email', $request->email)->first();
         $access = UserBack::where('user_id',$user->id)->first();

         return response()->json([
            'statuscode'=>200,
            'status' => true,
            'user'   => $user,
            'access'   => $access,
            'message' => "Akwaba sur FastPay",
         ], 200);
       }
       //edit user
       public function useredit(Request $request)
       {

          $payment = $request->payment;
          $parametre = $request->parametre;
          $solde = $request->solde;
          $nom = $request->nom;
          $email = $request->email;
          $tel = $request->tel;
          $password = $request->password;
          $user_id = $request->user_id;

           //email
           $validateemail = Validator::make($request->All(),[
            'email'=> 'email | unique:users',
           ]);
            if ($validateemail->fails()) {
                //en case d'erreur ou serveur n'est pas disponible
                return response()->json(['statuscode'=>'406',
                                        'status'=>false,
                                        'message'=>'cet email est déjà utilisé',
                                        'data'=> '',
                                        'error'=> $validateemail->errors(),
                                        ],406);
            }

            //tel
            $validatetel = Validator::make($request->All(),[
                'tel'=> 'required | unique:users',
            ]);
            if ($validatetel->fails()) {
                //en case d'erreur ou serveur n'est pas disponible
                return response()->json(['statuscode'=>'406',
                                        'status'=>'false',
                                        'message'=>'ce numéro est déjà utilisé',
                                        'data'=> '',
                                        'error'=> $validatetel->errors(),
                                        ],406);
            }


          if($payment){
            UserBack::where('user_id',$user_id)->update(['payment'=>$payment]);
          }
          if($parametre){
            UserBack::where('user_id',$user_id)->update(['prametres'=>$parametre]);
          }
          if($solde){
            UserBack::where('user_id',$user_id)->update(['solde'=>$solde]);
          }
          if($nom){
            User::where('id',$user_id)->update(['name'=>$nom]);
          }
          if($email){
            User::where('id',$user_id)->update(['email'=>$email]);
          }
          if($tel){
            User::where('id',$user_id)->update(['tel'=>$tel]);
          }
          if($password){
            User::where('id',$user_id)->update(['password'=> Hash::make($password)]);
            UserBack::where('user_id',$user_id)->update(['passwordfirst'=>$password]);
          }

          return response()->json(['statuscode'=>'200',
                                    'status'=>'true',
                                    'message'=>'mise à jour effectué avec succès'
                                ],200);
       }

       //locked user
       function locked(Request $request)
       {
            $user_id = $request->user_id;
            $stat = $request->stat;
            // dd($stat);
            if($stat){
                UserBack::where('user_id',$user_id)->update(['locked'=>$stat]);
            }
            return response()->json(['statuscode'=>'200',
                                    'status'=>'true',
                                    'message'=>"etat mise à jour avec succès"
                                ],200);
       }

       //connectin user
       
       /**
         * ---------------
         * ABOUT CLIENT
         * ---------------
         */
        //get about infos
        public function getabout(Request $request){
            // return About::all();
            return response()->json(['statuscode'=>200,
                                        'status'=>true,
                                        'about'=> About::all(),
                                        'paysetting'=> Setting::all(),
                                        'message'=>'configuration',
                                    ],200);
        }
        //update about infos
        public function updatinfos(Request $request){
           $nom = $request->nom;
           $logo = $request->file('logo');
           $tel = $request->tel;
           $email = $request->email;
           $adresse = $request->adresse;
           $details = $request->details;

        //   dd($nom);

           if($nom){
            About::where('id',1)->update(['nom'=>$nom]);
           }

          if($tel){
            About::where('id',1)->update(['tel'=>$tel]);
          }

          if($adresse){
            About::where('id',1)->update(['adresse'=>$adresse]);
          }

          if($details){
            About::where('id',1)->update(['details'=>$details]);
          }

          if($email){
            About::where('id',1)->update(['email'=>$email]);
          }

          if($logo){
                $lien = env('LIEN_FILE');
                $img_file = $request->file('logo');
                $img_id = $request->img_id;
                #File
                $path = $img_file->store('about','public');
                $img = $lien.$path;
                #Update
                About::where('id',1)->update(['logo'=>$img]);
          }
          return response()->json(['statuscode'=>'200',
                                    'status'=>'true',
                                    'message'=>"Mise à jour avec succès"
                                ],200);

        }


}
