<?php

namespace App\Http\Controllers;
include_once base_path('app/AdminActivityLogger.php');
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ApiModel;
use Illuminate\Support\Facades\DB;
class ApiController extends Controller
{
    /**
     * Retrieve the user for the given ID.
     *
     * @param  int  $id
     * @return Response
     */

    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function checkIn(Request $request)
    {
        // full name with space
        // mobile no
        // patient category ip or op
        // uhid
        // admission date (timestamp) // optional incase user is ip

        $data = $request->all();


        /*$res1 = $this->checkUserisOnline(6161);
        $radacctObj = DB::table('radacct')
                        ->where('username', '=', 6161)
                        ->first();
        if(!empty($radacctObj))
        {
            $mac = $radacctObj->callingstationid;
            //shell_exec('/usr/bin/sudo /usr/sbin/chilli_query logout "'.$mac.'"');
        }

        echo '/usr/bin/sudo /usr/sbin/chilli_query logout "'.$mac.'"';

        var_dump($res1);

        die;*/
        
        if(!empty($data))
        {
            try {

                    $messages = $this->validate($request, [
                        'uhid'              => 'required',
                        //'full_name'         => 'required',
                        //'email'           => 'required|email|unique:users,email',
                        //'email'             => 'required|email',
                        'sub_category'  => 'required',
                        //'plan_name'         => 'required',
                        //'mobile_number'     => 'required|integer|min:10',
                        'admission_date'    => 'sometimes|required|integer|min:10'
                    ]);

                    
                    /*if($data["patient_category"]==1)
                    {
                        $insertArry["planname"]     = config('constants.IP_PLAN');
                    }
                    else 
                    {
                        $insertArry["planname"]     = config('constants.OP_PLAN');
                    }*/

                    $insertArry["sub_category"]     = $data["sub_category"];
                
                    //$nameArry = explode(" ", $data["full_name"]);
                      
                    $insertArry["firstName"]    = (string)$data["uhid"];   
                    //$insertArry["lastName"]     = $nameArry[1];   
                    $insertArry["roomno"]       = $data["uhid"];                         
                    $insertArry["patient_category"]    = $data["patient_category"];   
                    //$insertArry["mobile_number"]       = $data["mobile_number"];   
                    $insertArry["admission_date"]      = $data["admission_date"];   

                    $insertArry["password"]     = $this->generateRandomPaasword();
                     
                    $userInfoData = DB::table('userinfo')
                        ->where('username', '=', (string)$data["uhid"])
                        ->first();
                    if(empty($userInfoData))
                    {
                        ApiModel::insertUser($insertArry);


                    } 
     
                    else 
                    {
                        // check user is online

                        $onlineStatus = $this->checkUserisOnline($data["uhid"]);

                        if($onlineStatus)
                        {
                            $this->logoutUser($data["uhid"]);
                        }


                        DB::table('userinfo')                                
                                ->where('username', '=', (string)$data["uhid"])
                        ->update(array(
                                
                                'admission_date' => date("Y-m-d H:i:s",$data["admission_date"]),
                                'updatedate'     => Carbon::now() 
                          ));

                         DB::table('radcheck')
                                ->where('attribute', '=', 'Cleartext-Password')
                                ->where('username', '=', (string)$data["uhid"])
                                ->update(array('value' => $insertArry["password"]));

                    }

                        logAdminActivity(
                        null, 
                        auth()->user()->email, 
                        'check_in', 
                        "New Patient Check-In | UHID: {$data['uhid']} | Category: {$data['patient_category']} | Plan: {$data['sub_category']} | Date: " . date("Y-m-d H:i:s", $data['admission_date']), 
                        'ApiController', 
                        'success', 
                        'patient', 
                        $data['uhid']
                    );

                    $groupCheckData = DB::table('radgroupcheck')
                        ->where('groupname', '=', $data["sub_category"])
                        ->where('attribute', '=', "Simultaneous-Use")
                        ->first(); 
                    if(!empty($groupCheckData))
                    {
                        DB::table('radusergroup')->where('username', '=', (string)$data["uhid"])
                                ->update(array('groupname' => $data["sub_category"]));
                        $simultaneoususe = (int)$groupCheckData->value;
                    }
                    else {
                         $simultaneoususe = (int)0;
                     } 
                    //ApiModel::insertUser($insertArry);


                    return  response()->json(['status' => true,'message' => 'CheckedIn Details Added Successfully.', "data"=>["uhid"=>(string)$data["uhid"],"password"=>$insertArry["password"], "simultaneous-use"=>$simultaneoususe ] ] );

            }
             
            catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'errors' => $e->validator->errors(),
                ], 500);
            }
                

           
                
                
        }
        else
        {
            return  response()->json(['status' => false,'message' => 'Required Parameters not found.', "data"=>[]]);    
        }




    }

    public function extendCheckIn(Request $request)
    {
        $data = $request->all();

        if(!empty($data))
        {
            $data["planname"] = "universe";
            ApiModel::extendCheckIn($data);
            return  response()->json(['status' => true,'message' => 'Checkedout Date Updated Successfully.', "data"=>[]]);
        }
        else
        {
            return  response()->json(['status' => false,'message' => 'Required Parameters not found.', "data"=>[]]);    
        }

    }

    public function checkOut(Request $request)
    {
        $data = $request->all();

        if(!empty($data))
        {
                
            try{
                
                $messages = $this->validate($request, [
                        'uhid'              => 'required',                    
                        'checkout_date'     => 'required|integer|min:10'
                    ]);

                $data["checkout_date"] = date("j M Y",$data["checkout_date"]);
                ApiModel::checkOut($data);

                logAdminActivity(
                null, 
                auth()->user()->email, 
                'check_out', 
                'Patient Check-Out UHID: ' . $data['uhid'], 
                'ApiController', 
                'success', 
                'patient', 
                $data['uhid']
            );


                $onlineStatus = $this->checkUserisOnline($data["uhid"]);
                if($onlineStatus)
                {
                    $this->logoutUser($data["uhid"]);
                }
                return  response()->json(['status' => true,'message' => 'Checkedout Successfully.', "data"=>[]]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'errors' => $e->validator->errors(),
                ], 500);
            }
        }
        else
        {
            return  response()->json(['status' => false,'message' => 'Required Parameters not found.', "data"=>[]]);    
        }

    }
    
    public function testFunc()
    {
	     return  response()->json(['status' => true,'message' => 'Test Function.', "data"=>[]]);
    }

    public function shiftRoom(Request $request)
    {

	    $data = $request->all();
	    
        if(!empty($data))
        {
	    $oldRoomNo = (string)$data["old_roomno"];
	    $newRoomNo = (string)$data["roomno"];
             $lastName = $data["lastName"];	    
	    $radcheckExist = ApiModel::shiftRoom($oldRoomNo,$newRoomNo,$lastName);
            

                return  response()->json(['status' => true,'message' => 'Room shifted Successfully.', "data"=>[]]);
        }
        else
        {
            return  response()->json(['status' => false,'message' => 'Required Parameters not found.', "data"=>[]]);
        }




    }

    public function changePlan(Request $request)
    {
        $data = $request->all();

        if(!empty($data))
        {
                
            try{               

                $messages = $this->validate($request, [
                        'uhid'       => 'required',                    
                        'sub_category'   => 'required'
                    ]);

                
                ApiModel::changePlan($data);

            //  GET OLD DETAILS BEFORE UPDATE
            $oldPlan = DB::table('radusergroup')
                        ->where('username', (string)$data['uhid'])
                        ->value('groupname');

            // ApiModel::changePlan($data);

            // LOG 
            logAdminActivity(
                null, 
                auth()->user()->email, 
                'change_plan', 
                "Plan changed for UHID: {$data['uhid']}. Old: {$oldPlan}  New: {$data['sub_category']}", 
                'ApiController', 
                'success', 
                'plan', 
                $data['uhid']
            );


                $onlineStatus = $this->checkUserisOnline($data["uhid"]);
                if($onlineStatus)
                {
                    $this->logoutUser($data["uhid"]);
                }

                $groupCheckData = DB::table('radgroupcheck')
                        ->where('groupname', '=', $data["sub_category"])
                        ->where('attribute', '=', "Simultaneous-Use")
                        ->first(); 
                if(!empty($groupCheckData))
                {
                    $simultaneoususe = (int)$groupCheckData->value;
                }
                else {
                     $simultaneoususe = (int)0;
                 } 

                return  response()->json(['status' => true,'message' => 'Plan Changed Successfully.', "data"=>["simultaneous-use"=>$simultaneoususe]]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'errors' => $e->validator->errors(),
                ], 500);
            }
        }
        else
        {
            return  response()->json(['status' => false,'message' => 'Required Parameters not found.', "data"=>[]]);    
        }

    }

    public function generateRandomPaasword()
    {
        $alphabet    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass        = array(); 
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 4; $i++) {
            $n = rand(0, $alphaLength);            
            $pass[] = $alphabet[$n];
        }

        $userPassword = implode($pass); 

        $checkGivenPasswrdInDB = DB::table('radcheck')
                        ->where('value', '=', (string)$userPassword)
                        ->first();
        if(!empty($checkGivenPasswrdInDB))
        {
            $this->generateRandomPaasword();
        }
        else
        {
            return implode($pass);    
        }   
         
    }


    public function checkUserisOnline($userName)
    {      

        $onlineCmd = "sudo chilli_query list | grep ".$userName." | cut -d \" \" -f 3";
        $cc = trim(shell_exec($onlineCmd));


        if($cc=="pass")
        {
            return (bool)true;
        }
        else
        {
            
            return (bool)false;
        }

    }

    public function logoutUser($userName)
    {      

        //$logoutCmd = "sudo chilli_query logout ".$userName;
        //$cc = shell_exec($logoutCmd);

        $radacctObj = DB::table('radacct')
                        ->where('username', '=', (string)$userName)
                        ->first();
        if(!empty($radacctObj))
        {
            $mac = $radacctObj->callingstationid;
            shell_exec('/usr/bin/sudo /usr/sbin/chilli_query logout "'.$mac.'"');
        }


    }


    public function resendPassword(Request $request)
    {   

        $data = $request->all();

        if(!empty($data))
        {
                
            try{
                
                $messages = $this->validate($request, [
                        'uhid'              => 'required'
                    ]);

                $password  = $this->generateRandomPaasword();

                // check user is online

                $onlineStatus = $this->checkUserisOnline($data["uhid"]);

                if($onlineStatus)
                {
                    $this->logoutUser($data["uhid"]);
                }                        

                 DB::table('radcheck')
                        ->where('attribute', '=', 'Cleartext-Password')
                        ->where('username', '=', (string)$data["uhid"])
                        ->update(array('value' => $password));



                return  response()->json(['status' => true,'message' => 'Request processed Successfully.', "data"=>[]]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'errors' => $e->validator->errors(),
                ], 500);
            }
        }
        else
        {
            return  response()->json(['status' => false,'message' => 'Required Parameters not found.', "data"=>[]]);    
        }






    }
    

}
 