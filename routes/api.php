<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * ------------------
 *  API FASTPAY
 * ------------------
 */

      
    /**
     * -----------------
     *   CLIENT::PAYMENT
     * -----------------
     */ 
        //Payment days
        Route::match(['GET','POST'],'dayspay',[ClientController::class, 'dayspay']);
        //Payment month
        Route::match(['GET','POST'],'monthpay',[ClientController::class, 'monthpay']);
        //Payment inscription
        Route::match(['GET','POST'],'inscriptionPay',[ClientController::class, 'inscriptionPay']);
        //check payment client::generate otp
        Route::match(['GET','POST'],'checkclientPay',[ClientController::class, 'checkclientPay']);
        //check otp :: get history
        Route::match(['GET','POST'],'storyPay/{code_otp}',[ClientController::class, 'storyPay']);

        
    /**
     * --------------------
     * PAIEMENT CINETPAY
     * --------------------
     */
      //Traitemnet du notify :: PAY IN
      Route::match(["get","post"],'notify_pay',[ClientController::class,'notify_pay']);
      //Traitement du return ::  PAY IN
      Route::match(["get","post"],'return_pay',[ClientController::class,'return_pay']);
      //Traitement du notify :: PAY OUT
      Route::match(["get","post"],'payout_gyms',[ClientController::class,'payout_gyms']);
      //Traitement du return :: PAY OUT
      Route::match(["get","post"],'payout_admin',[ClientController::class,'payout_admin']);

              
    
       

    /**
     * -----------
     * ADMIN API
     * -----------
     */

      /**
       * ---------------
       * STAT GLOBALE
       * ---------------
       */
        //STATS GLOBAL
        Route::match(['GET','POST'],'statsglobal',[AdminController::class, 'statsglobal']);


     
      /**
       * ---------------
       * COMPTE USER
       * ---------------
       */
        //creation de compte
        Route::match(['GET','POST'],'createuserCompte',[AdminController::class, 'createuserCompte']);
        //user story
        Route::match(['GET','POST'],'userstory',[AdminController::class, 'userstory']);
        //edit user
        Route::match(['PUT','POST'],'useredit',[AdminController::class, 'useredit']);
        //bloquer un user
        Route::match(['GET','POST'],'locked/{user_id}/{stat}',[AdminController::class, 'locked']);
        //client
        Route::match(['GET','POST'],'clientstory',[AdminController::class, 'clientstory']);
        //login user
        Route::match(['GET','POST'],'loginfastpay',[AdminController::class, 'loginfastpay']);

        





      /**
       * ------------------
       * SETTING PAYEMENT
       * -----------------
       */
         Route::match(['GET','POST'],'paysetting',[AdminController::class, 'paysetting']);

      

      /**
       * ---------------
       * PAYMENT STORY
       * ---------------
       */
        //Day payment story
        Route::match(['GET','POST'],'daypaystory',[AdminController::class, 'daypaystory']);
        //Month payment story
        Route::match(['GET','POST'],'monthpaystory',[AdminController::class, 'monthpaystory']);
        //Inscription payment story
        Route::match(['GET','POST'],'inscriptionpaystory',[AdminController::class, 'monthpaystory']);
        //story payement between two date by payment type
        Route::match(['GET','POST'],'paystat/{type_pay}/{start}/{end}',[AdminController::class, 'paystat']);
        //get settings pay
        Route::match(['GET','POST'],'getpaysetting',[AdminController::class, 'getpaysetting']);

      
      /**
       * ---------------
       * PAYMENT OUT
       * ---------------
       */
         //payout user :: retrait utilisateur
         Route::match(['GET','POST'],'payoutuser',[AdminController::class, 'payoutuser']);
         //payout user story
         Route::match(['GET','POST'],'payoutuserStory',[AdminController::class, 'payoutuserStory']);
         //payout fastpay :: retrait fastpay
         Route::match(['GET','POST'],'fastpayout',[AdminController::class, 'fastpayout']);
         //payout fastpay story
         Route::match(['GET','POST'],'fastpayoutstory',[AdminController::class, 'fastpayoutstory']);
         //fastpay in story
         Route::match(['GET','POST'],'fastpayinstory',[AdminController::class, 'fastpayinstory']);
         //fastpay solde
         Route::match(['GET','POST'],'solde_stat',[AdminController::class, 'solde_stat']);


      /**
       * ---------------
       * ABOUT CLIENT
       * ---------------
       */
        //get about infos
        Route::match(['GET','POST'],'getabout',[AdminController::class, 'getabout']);
        //update about infos
        Route::match(['GET','POST'],'updatinfos',[AdminController::class, 'updatinfos']);

        


     


    

      
