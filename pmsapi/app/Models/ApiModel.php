<?php

namespace App\Models;



use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApiModel extends Model
{
    
    public static function insertUser($form_data)
    {
        
        $dateToTimestamp1 = strtotime($form_data['checkoutDate']);
        $dateToTimestamp2 = strtotime('+1 day', $dateToTimestamp1);
        $radcheck_data = array(
                               
                                /*array(
                                        'username' => $form_data['roomno'],
                                        'attribute' => 'Auth-Type',
                                        'op' => ':=',
                                        'value' => 'Accept',
				),*/ 
                                array(
                                        'username' => (string)$form_data['roomno'],
                                        'attribute' => 'Cleartext-Password',
                                        'op' => ':=',
                                        'value' => strtolower($form_data['firstName']),
                                ),
                                array(
                                    'username' => (string)$form_data['roomno'],
                                    'attribute' => 'Expiration',
                                    'op' => ':=',
                                    'value' => date("d M Y",$dateToTimestamp2),
                                ),
                            );
        DB::table('radcheck')->insert($radcheck_data);

        DB::table('radusergroup')
                                ->insert(array(
                                    'username' => (string)$form_data['roomno'],
                                    'groupname' => $form_data['planname'],
                                    'priority' => 1,
				));

	$userInfoData = DB::table('userinfo')
                        ->where('username', '=', (string)$form_data['roomno'])
                        ->first();
	if(!empty($userInfoData))
	{
		                DB::table('userinfo')                                
                                ->where('username', '=', (string)$form_data['roomno'])
				->update(array(
					        'firstname'  => $form_data['firstName'],
						'lastname'   => $form_data['lastName'] ,
						'notes'      => $form_data['checkoutDate'] ,
						'updatedate' => Carbon::now() 
				  ));
	}
	else
	{
	                        DB::table('userinfo')
                                ->insert(array(
                                    'username' 	   => (string)$form_data['roomno'],
                                    'firstname'    => $form_data['firstName'],
                                    'lastname'     => $form_data['lastName'],
                                    'notes'        => $form_data['checkoutDate'],
                                    'creationdate' => Carbon::now(),
                                    'updatedate'   => Carbon::now(),
                                    'creationby'   => 1,
				));
       }

    }

    public static function extendCheckIn($form_data)
    {
        $radcheckData = DB::table('radcheck')
                    ->where('attribute', '=', 'Expiration')
                    ->where('username', '=', (string)$form_data['roomno'])
                    ->get();
        if($radcheckData->isNotEmpty())
        {
	    
	    $dateToTimestamp1 = strtotime($form_data['checkoutDate']);
            $dateToTimestamp2 = strtotime('+1 day', $dateToTimestamp1);	
	    DB::table('radcheck')
                                ->where('attribute', '=', 'Expiration')
                                ->where('username', '=', (string)$form_data['roomno'])
                                ->update(array('value' => date("d M Y",$dateToTimestamp2)));

            $userinfo = DB::table('userinfo')
                    
                    ->where('username', '=', (string)$form_data['roomno'])
                    ->get();                    
            if($userinfo->isNotEmpty())
            {        
                DB::table('userinfo')
                                
                                ->where('id', '=', $userinfo[0]->id)
                                ->update(array('notes' => date("d M Y",$dateToTimestamp2) ));     
            }     


        }            

    }


    public function checkOut($form_data)
    {

        $radcheckData = DB::table('radcheck')
                    ->where('attribute', '=', 'Cleartext-Password')
                    ->where('username', '=', (string)$form_data['roomno'])
                    ->get();
        if($radcheckData->isNotEmpty())
        {                      

            /*DB::table('radcheck')            
            ->where('id', '=', $radcheckData[0]->id)
            ->update(array('value' => 'Reject'));*/

            DB::table('radcheck')
            ->where('username', '=', (string)$form_data['roomno'])
            ->delete();

            DB::table('radusergroup')
            ->where('username', '=', (string)$form_data['roomno'])
            ->delete();

            /* DB::table('radusergroup')
            ->where('username', '=', $form_data['roomno'])
            ->delete();*/


        }            


    }

    public static function checkEntryExist($roomNo)
    {
        $radcheckData = DB::table('radcheck')
                    ->where('attribute', '=', 'Cleartext-Password')
                    ->where('username', '=', (string)$roomNo)
                    ->get();
        return $radcheckData;            
    }


    public static function updateCheckinEntries($form_data,$primaryKey)
    {
        DB::table('radcheck')
        ->where('attribute', '=', 'Expiration')
        ->where('username', '=', (string)$form_data['roomno'])
        ->update(array('value' => date("d M Y",strtotime($form_data['checkoutDate']))));

        DB::table('radcheck')
        ->where('attribute', '=', 'Cleartext-Password')
        ->where('username', '=', (string)$form_data['roomno'])
        ->update(array('value' => strtolower($form_data['firstName'])));

        DB::table('userinfo')
                                ->where('username', '=', (string)$form_data['roomno'])
                                ->update(array(
                                                'firstname'  => $form_data['firstName'],
                                                'lastname'   => $form_data['lastName'] ,
                                                'notes'      => $form_data['checkoutDate'] ,
                                                'updatedate' => Carbon::now()
                                  ));




    }

    public static function  shiftRoom($oldRoomno,$newRoomno,$firstName)
    {

	 //\DB::connection()->enableQueryLog();
	DB::table('radcheck')
        ->where('username',(string) $oldRoomno)
	->update(array('username'=>(string)$newRoomno));

	    //$quer = \DB::getQueryLog();
	    //echo "<pre>";print_r($quer);echo "</pre>";
	   // DB::statement("update radcheck set username = 100 where username = 300");
       // die;
        DB::table('radcheck')
	->where('username', '=', (string)$newRoomno)
	->where('attribute', '=', "Cleartext-Password")
        ->update(array('value'=>strtolower((string)$firstName)));

        $oldRoomUserInfoData = DB::table('userinfo')
                        ->where('username', '=',(string)$oldRoomno)
                        ->first();
	$newRoomUserInfoData = DB::table('userinfo')
                        ->where('username', '=',(string)$newRoomno)
			->first();

	

        if(!empty($oldRoomUserInfoData))
	{
	                        DB::table('userinfo')
                                ->where('username', '=', $oldRoomno)
                                ->update(array(
					        'username'   => $newRoomno,
					        'firstname'  => $oldRoomUserInfoData->firstname,
                                                'lastname'   => $oldRoomUserInfoData->lastname ,
                                                'notes'      => $oldRoomUserInfoData->notes ,
                                                'updatedate' => Carbon::now()
                                  ));
        }
        /*else
        {
                                DB::table('userinfo')
                                ->insert(array(
                                    'username'     => $oldRoomUserInfoData->username,
                                    'firstname'    => $oldRoomUserInfoData->firstname,
                                    'lastname'     => $oldRoomUserInfoData->lastname,
                                    'notes'        => $oldRoomUserInfoData->notes,
                                    'creationdate' => Carbon::now(),
                                    'updatedate'   => Carbon::now(),
                                    'creationby'   => 1,
                                ));
	}*/




        DB::table('radusergroup')
        ->where('username', '=', (string)$oldRoomno)
        ->update(array('username'=>(string)$newRoomno));

        

    }


    



                            

}
