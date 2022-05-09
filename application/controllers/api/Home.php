<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Home extends CI_Controller {
    function __construct()
    {
        error_reporting(0);
        parent::__construct();
        $timezoneDB = $this->db_model->select_data('timezone','site_details',array('id'=>1));
        if(isset($timezoneDB[0]['timezone']) && !empty($timezoneDB[0]['timezone'])){
            date_default_timezone_set($timezoneDB[0]['timezone']);
        }
		
			// check select language
		$this->load->helper('language');
		$language = $this->general_settings('language_name');
		if($language=="french"){
			$this->lang->load('french_lang', 'french');
		}else if($language=="arabic"){
			$this->lang->load('arabic_lang', 'arabic');
		}else{
			$this->lang->load('english_lang', 'english');
		}
    }
	
	
	function general_settings($key_text=''){
		$data = $this->db_model->select_data('*','general_settings',array('key_text'=>$key_text),1);
		return $data[0]['velue_text'];
	}
	
	function general_setting($key_text=''){
	    $data=array();
	    $data['languageName']=$this->general_settings('language_name');
	    $data['currencyCode']=$this->general_settings('currency_code');
	    $data['currencyDecimalCode']=$this->general_settings('currency_decimal_code');
	    $data['paymentType']=$this->general_settings('payment_type');
	    $data['razorpayKeyId']=$this->general_settings('razorpay_key_id');
	    $data['razorpaySecretKey']=$this->general_settings('razorpay_secret_key');
	    $data['paypalClientId']=$this->general_settings('paypal_client_id');
	    $data['paypalSecretKey']=$this->general_settings('paypal_secret_key');
		$arr = array(
                    'status'=>'true',
                    'data'=>$data
                        );
        echo json_encode($arr);
	}
	
    function chekLogin(){
        $data = $_REQUEST;
        if(isset($data['student_id']) && isset($data['token'])){
            $student_id = trim($data['student_id']);
            $token  =  trim($data['token']);
            // $batch_id  =  trim($data['batch_id']);
            $studentDetails = $this->db_model->select_data('login_status,status,token','students use index (id)',array('id'=>$student_id),1);
            $batch_details = $this->db_model->select_data('id,status','batches use index (id)',array('id'=>$batch_id),1);
            // if(($studentDetails[0]['status'] == 1) && ($studentDetails[0]['token'] == $token) && ($batch_details[0]['status'] == 1))
            // {
            if(($studentDetails[0]['status'] == 1) && ($studentDetails[0]['token'] == $token))
            {
                if($studentDetails[0]['login_status'] == 0){
                    $arr = array(
                        'status'=>'false',
                        'msg'=>$this->lang->line('ltr_logout')
                    ); 
                }else{
					$l =$this->general_settings('language_name');
                    $arr = array(
                            'status'=>'true',
                            'msg'=>$this->lang->line('ltr_login_continue'),
							'languageName' => $l
							
                        );
                }
            }else{
                $arr = array(
                        'status'=>'false',
                        'msg'=>$this->lang->line('ltr_logout')
                    ); 
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
    function login(){
        $data = $_REQUEST;
        if(isset($data['username']) && isset($data['password']) && isset($data['versionCode'])  && isset($data['token'])){
            $uname = trim($data['username']);
            $pass = md5(trim($data['password']));
            $version= trim($data['versionCode']);
            $token= trim($data['token']);
            $enroll_id = strtoupper($uname);
           
            if(!filter_var($enroll_id, FILTER_VALIDATE_EMAIL)){
                    $stud_cond = array('enrollment_id'=>$enroll_id,'password'=>$pass);
                }else{      
                    $stud_cond = array('email'=>$enroll_id,'password'=>$pass);
                }
            $studentDetails = $this->db_model->select_data('*','students use index (id)',$stud_cond,1);
           
            if(!empty($studentDetails)){
                if($studentDetails[0]['status'] == '1'){
                    $batch_details = $this->db_model->select_data('id,batch_name,batch_type','batches use index (id)',array('status'=>1,'id'=>$studentDetails[0]['batch_id']),1);
                    // if(!empty($batch_details)){
                        $studentData = array(
                            'studentId' => $studentDetails[0]['id'], 
                            'userEmail' => $studentDetails[0]['email'],
                            'fullName' => $studentDetails[0]['name'],
                            'enrollmentId' => $studentDetails[0]['enrollment_id'],
                            'image' => base_url('uploads/students/').$studentDetails[0]['image'],
                            'mobile' => $studentDetails[0]['contact_no'],
                            'versionCode' => $studentDetails[0]['app_version'],
                            'batchId' => $studentDetails[0]['batch_id'],
                            'batchName'=>'',
                            'adminId' => $studentDetails[0]['admin_id'],
							'paymentType' => $this->general_settings('payment_type'),
                            'admissionDate' => date('d-m-Y',strtotime($studentDetails[0]['admission_date'])),
							'languageName'=>$this->general_settings('language_name')
                        );
						
						$fee_details = $this->db_model->select_data('*','student_payment_history',array('student_id'=>$studentDetails[0]['id'],'batch_id'=>$studentDetails[0]['batch_id']));
					
						    $studentData['transactionId'] = !empty($fee_details[0]['transaction_id'])?$fee_details[0]['transaction_id']:'';
					
        				$studentData['amount'] = !empty($fee_details[0]['amount'])?$fee_details[0]['amount']:'';
                        //update app version and login status                  
                        $this->db_model->update_data_limit('students use index (id)',array('login_status'=>1,'app_version'=>$version,'token'=>$token),array('id'=>$studentDetails[0]['id']),1);
                        $arr = array(
                                'studentData' => $studentData,
                                'status' => 'true',
                                'msg' => $this->lang->line('ltr_welcome').' '.$studentDetails[0]['name']
                            );
                    // }else{
                    //     $arr = array('status' => 'false', 'msg'=>$this->lang->line('ltr_batch_in_msg'));
                    // }
                }else{
                    $arr = array('status' => 'false', 'msg'=>$this->lang->line('ltr_contact_to_admin_msg'));
                }
            }else{
                $arr = array('status' => 'false', 'msg'=> $this->lang->line('ltr_invalid_c'));
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
	function logout(){
        $data = $_REQUEST;
        if(isset($data['student_id'])){
            $student_id = $data['student_id'];
            $ins = $this->db_model->update_data_limit('students use index (id)',array('login_status'=>0),array('id'=>$student_id),1);
            if($ins){
                 $arr =	array('status'=>'true','msg'=>$this->lang->line('ltr_logged_out'));
            }else{
                 $arr =	array('status'=>'false','msg'=>$this->lang->line('ltr_failed_out'));
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
       	echo json_encode($arr);
    }
	
	function student_registration(){
			$data = $_REQUEST;
				$this->db_model->insert_data('temp_data',array('temp'=>json_encode($data)));
			if(!empty($data['name']) && !empty($data['email']) && !empty($data['mobile'])){
				// $checkBatch =$this->db_model->select_data('id','batches use index (id)',array('status'=>1));
			
				// if(!empty($checkBatch)){
				    
					$admin_id =$this->db_model->select_data('id','users use index (id)',array('role'=>1),1)[0]['id'];
					
				// 	$prevRecd = $this->db_model->select_data('id as studentId,email as userEmail,name as fullName,enrollment_id as enrollmentId,contact_no as mobile,app_version as versionCode, batch_id as batchId,admin_id as adminId,admission_date as admissionDate, image, token','students use index (id)',array('admin_id'=>$admin_id,'email'=>$this->input->post('email',TRUE)),1);
				
					$prevRecd = $this->db_model->select_data('id','students',array('email'=>$data['email'], 'contact_no'=>$data['mobile']));
				
					if(empty($prevRecd)){
						$siteData = array();
						$siteData['word_for_enroll'] = $this->common->enrollWord;
						$data_arr['admin_id'] = $admin_id;            
						$data_arr['login_status'] = 0;
						$lastrecord = $this->db_model->select_data('id','students use index (id)',array('admin_id'=>$admin_id),1,array('id','desc'));             
						if(!empty($lastrecord)){
							$last_id = $lastrecord[0]['id'];
						}else{
							$last_id = 0;
						}
						
						$password = $siteData['word_for_enroll'].$admin_id.$last_id.rand(1000,5000);
						$enrolid = $siteData['word_for_enroll'].$admin_id.$last_id.rand(10,100);
						$data_arr['name'] = $data['name'];
						$data_arr['email'] = $data['email'];
						$data_arr['batch_id'] = $data['batch_id'];
						$data_arr['added_by'] = 'student';
						$data_arr['status'] = 1;
						$data_arr['enrollment_id'] = $enrolid;
						$data_arr['password'] = md5($password);
						$data_arr['admission_date'] = date('Y-m-d');
						$data_arr['image']='student_img.png';
						$data_arr['contact_no']=$this->input->post('mobile',TRUE);
						//update app version and login status
						$data_arr['login_status']= 1;
						$data_arr['last_login_app']= date("Y-m-d H:i:s");
						$data_arr['app_version']= !empty($data['versionCode'])? trim($data['versionCode']):'';
						$data_arr['token']= !empty($data['token'])? trim($data['token']):'';
						
						$data_arr = $this->security->xss_clean($data_arr);
						$ins = $this->db_model->insert_data('students',$data_arr);
						if($ins){
							
							$studentData =$this->db_model->select_data('id as studentId,email as userEmail,name as fullName,enrollment_id as enrollmentId,contact_no as mobile,app_version as versionCode, batch_id as batchId,admin_id as adminId,admission_date as admissionDate, image, token','students use index (id)',array('id'=>$ins),1);
							$studentData[0]['batchName']='';
							$studentData[0]['image']= base_url('uploads/students/').$studentData[0]['image'];
							$studentData[0]['password'] = $password;
							$studentData[0]['paymentType'] = $this->general_settings('payment_type');
							$studentData[0]['languageName'] = $this->general_settings('language_name');
							
							 //check batch type
							$batch_type =$this->db_model->select_data('*','batches use index (id)',array('id'=>$data['batch_id']),1);
        				    if($batch_type[0]['batch_type']==2){
        				        $studentData[0]['batchName']= $batch_type[0]['batch_name'];
    							$data_pay=array(
    							           'student_id'=>$ins,
    								       'batch_id'=>$data['batch_id'],
    									   'transaction_id'=> !empty($data['transaction_id'])?$data['transaction_id']:'',
    										  'amount'=> !empty($data['amount'])?$data['amount']:'',
    											);
    							$data_pay = $this->security->xss_clean($data_pay);
    							$insf = $this->db_model->insert_data('student_payment_history',$data_pay);
            					$studentData[0]['transactionId'] = !empty($data['transaction_id'])?$data['transaction_id']:'';
            					
            				    $studentData[0]['amount'] = !empty($data['amount'])?$data['amount']:'';
        				    	$this->db_model->update_data_limit('students use index (id)',array('payment_status'=>1),array('id'=>$ins),1);
            				}else{
            				    $studentData[0]['batchName']= $batch_type[0]['batch_name'];
        				    	$studentData[0]['transactionId'] = 'free';
        				    	$studentData[0]['amount'] = !empty($amount)?$amount:'';
            				}
        				    
							$arr = array('status'=>'true', 'msg' =>$this->lang->line('ltr_account_created'),'studentData'=>$studentData[0]);
					//batch asin
							if(!empty($data['batch_id']) && $data['batch_id']>0){
						    $data_batch= array(
						                 'student_id'=>$ins,
						                 'batch_id'=>$data['batch_id'],
						                 'added_by'=>'student'
	        					                 );
					 	   $this->db_model->insert_data('sudent_batchs',$data_batch);
						}
						    // send email 
						   $title = $this->db_model->select_data('site_title','site_details','',1,array('id','desc'))[0]['site_title'];
                            $subj = $title.'- '.$this->lang->line('ltr_credentials');
                            $em_msg = $this->lang->line('ltr_hey').' '.ucwords($data['name']).', '.$this->lang->line('ltr_congratulation').' <br/><br/>'.$this->lang->line('ltr_successfully_enrolled').'<br/><br/>'.$this->lang->line('ltr_login_details').'<br/><br/> '.$this->lang->line('ltr_enrolment_id').' : '.$enrolid.'<br/><br/>'.$this->lang->line('ltr_password').' : '.$password.'';
                            $this->SendMail($data['email'], $subj, $em_msg);
                              
						}
					}else{
				// 		$siteData = array();
				// 	    $siteData['word_for_enroll'] = $this->common->enrollWord;
				// 		$data_arr['admin_id'] = $admin_id;   
				// 		$data_arr['login_status'] = 0;
				// 		$last_id = $prevRecd[0]['studentId'] ;
				// 		$password = $siteData['word_for_enroll'].$admin_id.$last_id.rand(1000,5000);
				// 		$enrolid = $prevRecd[0]['enrollmentId'];
				// 		$data_arr['name'] = $data['name'];
				// 		$data_arr['email'] = $data['email'];
				// 		$data_arr['batch_id'] = $data['batch_id'];
				// 		$data_arr['added_by'] = 'student';
				// 		$data_arr['status'] = 1;
				// 		$data_arr['enrollment_id'] = $enrolid;
				// 		$data_arr['password'] = md5($password);
				// 		$data_arr['contact_no']=$this->input->post('mobile',TRUE);
				// 		//update app version and login status
				// 		$data_arr['login_status']= 1;
				// 		$data_arr['last_login_app']= date("Y-m-d H:i:s");
				// 		$data_arr['app_version']= !empty($data['versionCode'])? trim($data['versionCode']):'';
				// 		$data_arr['token']= !empty($data['token'])? trim($data['token']):'';
						
				// 		$data_arr = $this->security->xss_clean($data_arr);
				// 		$this->db_model->update_data_limit('students',$data_arr,array('id'=>$last_id));
				
				// 		$prevRecd[0]['batchName']='';
				// 		$prevRecd[0]['image']= base_url('uploads/students/').$prevRecd[0]['image'];
				// 		$prevRecd[0]['password'] = $password;
				// 		$prevRecd[0]['paymentType'] = $this->general_settings('payment_type');
				// 		$prevRecd[0]['languageName'] = $this->general_settings('language_name');
						
				// 		 //check batch type
				// 		$batch_type =$this->db_model->select_data('*','batches use index (id)',array('id'=>$data['batch_id']),1);
    // 				    if($batch_type[0]['batch_type']==2){
    				        
				// 			$data_pay=array(
				// 			           'student_id'=>$last_id,
				// 				       'batch_id'=>$data['batch_id'],
				// 					   'transaction_id'=> !empty($data['transaction_id'])?$data['transaction_id']:'',
				// 						  'amount'=> !empty($data['amount'])?$data['amount']:'',
				// 							);
				// 			$data_pay = $this->security->xss_clean($data_pay);
				// 			$insg = $this->db_model->insert_data('student_payment_history',$data_pay);
    //     					$prevRecd[0]['transactionId'] = !empty($data['transaction_id'])?$data['transaction_id']:'';
    // 				    	$prevRecd[0]['amount'] = !empty($data['amount'])?$data['amount']:'';
    // 				    	$this->db_model->update_data_limit('students use index (id)',array('payment_status'=>1),array('id'=>$last_id),1);
    //     				}else{
    //     				    $prevRecd[0]['batchName']= $batch_type[0]['batch_name'];
    // 				    	$prevRecd[0]['transactionId'] = 'free';
    // 				    	$prevRecd[0]['amount'] = !empty($amount)?$amount:'';
    //     				}
    // 				    $prevRecd[0]['batchId']=$data['batch_id'];
				// 		$arr = array('status'=>'true', 'msg' =>$this->lang->line('ltr_account_created'),'studentData'=>$prevRecd[0]);
				// 		//batch asin
				// 		$data_batch= array(
				// 		                 'student_id'=>$last_id,
				// 		                 'batch_id'=>$data['batch_id'],
				// 		                 'added_by'=>'student'
	   //     					                 );
				// 		$this->db_model->insert_data('sudent_batchs',$data_batch);
				// 	    // send email 
				// 	   $title = $this->db_model->select_data('site_title','site_details','',1,array('id','desc'))[0]['site_title'];
    //                     $subj = $title.'- '.$this->lang->line('ltr_credentials');
    //                     $em_msg = $this->lang->line('ltr_hey').' '.ucwords($data['name']).', '.$this->lang->line('ltr_congratulation').' <br/><br/>'.$this->lang->line('ltr_successfully_enrolled').'<br/><br/>'.$this->lang->line('ltr_login_details').'<br/><br/> '.$this->lang->line('ltr_enrolment_id').' : '.$enrolid.'<br/><br/>'.$this->lang->line('ltr_password').' : '.$password.'';
    //                     $this->SendMail($data['email'], $subj, $em_msg);
                          
                	$arr = array(
					'status'=>'false',
					'msg'=>$this->lang->line('ltr_email_already_msg')
				);           
                          
					}
					
				// }else{
				// 	$arr = array(
				// 	'status'=>'false',
				// 	'msg'=>$this->lang->line('ltr_no_batch_available_r')
				// ); 
				// }
				
			}else{
				$arr = array(
					'status'=>'false',
					'msg'=>$this->lang->line('ltr_missing_parameters_msg')
				); 
			}
			echo json_encode($arr);
		}
		function checkLanguage(){
			$language =$this->general_settings('language_name');
			if(!empty($language)){
					$arr = array('status'=>'true', 'msg' =>$this->lang->line('ltr_fetch_successfully'),'languageName'=>$language);
					
				}else{
					$arr = array('status'=>'false', 'msg' => $this->lang->line('ltr_no_record_msg')); 
				}
			echo json_encode($arr);
		}
		
		
// 		function get_batch_fee(){
// 			$data = $_REQUEST;
          
			
// 	$batchData = $this->db_model->select_data('id, batch_name as batchName, start_date as startDate, end_date as endDate, start_time	as startTime, end_time as endTime, batch_type as batchType, batch_price as batchPrice, no_of_student as noOfStudent ,status,description, batch_image as batchImage, batch_offer_price as batchOfferPrice','batches use index (id)',array('status'=>1),'',array('id','desc'));

// 				if(!empty($batchData)){
// 					foreach($batchData as $key=>$value){
// 						if(!empty($value['batchImage'])){
// 							$batchData[$key]['batchImage']= base_url('uploads/batch_image/').$value['batchImage'];
// 						}
// 						$startDate =$value['startDate'];
// 						$endDate =$value['endDate'];
//                         $batchData[$key]['startDate']=date('d-m-Y',strtotime($startDate));
//                         $batchData[$key]['endDate']=date('d-m-Y',strtotime($endDate));
                       
// 						$batch_fecherd =$this->db_model->select_data('batch_specification_heading as batchSpecification, batch_fecherd as fecherd','batch_fecherd',array('batch_id'=>$value['id']));
// 						if(!empty($batch_fecherd)){
// 							   $batchData[$key]['batchFecherd'] =$batch_fecherd;
// 						}else{
// 							$batchData[$key]['batchFecherd']=array();
// 						}
	
// 					   // add payment type
// 					   $batchData[$key]['paymentType'] = $this->general_settings('payment_type');
// 					   $batchData[$key]['currencyCode'] = $this->general_settings('currency_code');
// 					   $batchData[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
					   
					   
// 					   $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$value['id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
// 					   if(!empty($batchSubject)){
// 					       foreach($batchSubject as $skey=>$svalue){
// 					            $cid=implode(', ',json_decode($svalue['chapter']));
// 					            $con ="id in ($cid)";
// 					            $chapter =$this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
// 					            if(!empty($chapter)){
// 					                foreach($chapter as $ckey=>$cvalue){
					                    
// 					                    $sub_like = array('topic,batch',urldecode($cvalue['chapterName']).',"'.$value['id'].'"');
//                 					    $sub_videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$sub_like);
//                                         if(!empty($sub_videos)){
//                                             foreach($sub_videos as $vkey=>$vvalue){
//                                                 $url = $vvalue['url'];
//                                                 preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
//                                                 $sub_videos[$vkey]['videoId']=$match[1];
//                                             }
//                                             $chapter[$ckey]['videoLectures']=$sub_videos;
//                                         }else{
//                                           $chapter[$ckey]['videoLectures'] = array(); 
//                                         }
					                    
					                    
// 					                }
// 					                $batchSubject[$skey]['chapter']=$chapter;
// 					            }else{
// 					             $batchSubject[$skey]['chapter']=array();   
// 					            }
					            
					            
// 					       }
					       
// 					       $batchData[$key]['batchSubject'] = $batchSubject;
// 					   }else{
// 					     $batchData[$key]['batchSubject'] = array();  
// 					   }
// 					    $like = array('batch','"'.$value['id'].'"');
// 					    $videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$like);
//                         if(!empty($videos)){
//                             foreach($videos as $vkey=>$vvalue){
//                                 $url = $vvalue['url'];
//                                 preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
//                                 $videos[$vkey]['videoId']=$match[1];
//                             }
//                             $batchData[$key]['videoLectures']=$videos;
//                         }else{
//                           $batchData[$key]['videoLectures'] = array(); 
//                         }
// 					   $student_batch_dtail = $this->db_model->select_data('*','sudent_batchs',array('student_id'=>$data['student_id'],'batch_id' => $value['id'] ),'');
// 					   if(!empty($student_batch_dtail)){
// 					        $batchData[$key]['purchase_condition'] = true;
// 					   }else{
// 					       $batchData[$key]['purchase_condition'] = false;
// 					   }
					   
// 					}
// 					$yourBatch=array();
// 					$recommendedBatch=array();
					
// 					if(!empty($data['student_id'])){
					    
// 					    $batchs_id =$this->db_model->select_data('batch_id','sudent_batchs',array('student_id'=>$data['student_id']));
					       
// 					    if(!empty($batchs_id)){
					        
// 					        $batchId=array();
// 					        foreach($batchs_id as $key=>$value){
// 					            $batchId[$key] =$value['batch_id'];
// 					        }
				
// 					    $batch_id =implode(', ',$batchId);
					  
// 					    if(!empty($batch_id)){
// 					    $cond="id in ($batch_id)";
// 					    }else{
// 					        $cond = '';
// 					    }
					   
// 					    $yourBatch = $this->db_model->select_data('id ,batch_name as batchName, start_date as startDate, end_date as endDate, start_time	as startTime, end_time as endTime, batch_type as batchType, batch_price as batchPrice, no_of_student as noOfStudent ,status,description, batch_image as batchImage, batch_offer_price as batchOfferPrice','batches use index (id)',$cond,'',array('id','desc'));
					 
					                        
// 					    if(!empty($yourBatch)){
					        
// 					        foreach($yourBatch as $key=>$value){
//         						if(!empty($value['batchImage'])){
//         							$yourBatch[$key]['batchImage']= base_url('uploads/batch_image/').$value['batchImage'];
//         						}
//         						$startDate =$value['startDate'];
//         						$endDate =$value['endDate'];
//                                 $yourBatch[$key]['startDate']=date('d-m-Y',strtotime($startDate));
//                                 $yourBatch[$key]['endDate']=date('d-m-Y',strtotime($endDate));
//         						$batch_fecherd =$this->db_model->select_data('batch_specification_heading as batchSpecification, batch_fecherd as fecherd','batch_fecherd',array('batch_id'=>$value['id']));
//         						if(!empty($batch_fecherd)){
//         							   $yourBatch[$key]['batchFecherd'] =$batch_fecherd;
//         						}else{
//         							$yourBatch[$key]['batchFecherd']=array();
//         						}
//         					   // add payment type
//         					   $yourBatch[$key]['paymentType'] = $this->general_settings('payment_type');
//         					   $yourBatch[$key]['currencyCode'] = $this->general_settings('currency_code');
//         					   $yourBatch[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
        					   
//         					   $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$value['id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
//         					   if(!empty($batchSubject)){
//         					       foreach($batchSubject as $skey=>$svalue){
//         					            $cid=implode(', ',json_decode($svalue['chapter']));
//         					            $con ="id in ($cid)";
        					            
//         					            $chapter =$this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
//         					            if(!empty($chapter)){
//         					                foreach($chapter as $ckey=>$cvalue){
        					                    
//         					                    $sub_like = array('topic,batch',urldecode($cvalue['chapterName']).',"'.$value['id'].'"');
//                         					    $sub_videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$sub_like);
//                                                 if(!empty($sub_videos)){
//                                                     foreach($sub_videos as $vkey=>$vvalue){
//                                                         $url = $vvalue['url'];
//                                                         preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
//                                                         $sub_videos[$vkey]['videoId']=$match[1];
//                                                     }
//                                                     $chapter[$ckey]['videoLectures']=$sub_videos;
//                                                 }else{
//                                                   $chapter[$ckey]['videoLectures'] = array(); 
//                                                 }
        					                    
        					                    
//         					                }
//         					                $batchSubject[$skey]['chapter']=$chapter;
//         					            }else{
//         					             $batchSubject[$skey]['chapter']=array();   
//         					            }
        					            
//         					       }
        					       
//         					       $yourBatch[$key]['batchSubject'] = $batchSubject;
//         					   }else{
//         					       $yourBatch[$key]['batchSubject'] = array();  
//         					   }
//         					    $like = array('batch','"'.$value['id'].'"');
//         					    $videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$like);
//                                 if(!empty($videos)){
//                                     foreach($videos as $vkey=>$vvalue){
//                                         $url = $vvalue['url'];
//                                         preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
//                                         $videos[$vkey]['videoId']=$match[1];
//                                     }
//                                     $yourBatch[$key]['videoLectures']=$videos;
//                                 }else{
//                                     $yourBatch[$key]['videoLectures'] = array(); 
//                                 }

        					   
//         					}
// 					    }
					        
// 					    }
// 					    if(!empty($batch_id)){
// 					    $cond="id not in ($batch_id) AND status=1";
// 					    }else{
// 					        $cond = '';         
// 					    }
					    
//                         $recommendedBatch = $this->db_model->select_data('id ,batch_name as batchName, start_date as startDate, end_date as endDate, start_time	as startTime, end_time as endTime, batch_type as batchType, batch_price as batchPrice, no_of_student as noOfStudent ,status,description, batch_image as batchImage, batch_offer_price as batchOfferPrice','batches use index (id)',$cond,'',array('id','desc'));
// 					    if(!empty($recommendedBatch)){
					        
// 					        foreach($recommendedBatch as $key=>$value){
//         						if(!empty($value['batchImage'])){
//         							$recommendedBatch[$key]['batchImage']= base_url('uploads/batch_image/').$value['batchImage'];
//         						}
//         						$startDate =$value['startDate'];
//         						$endDate =$value['endDate'];
//                                 $recommendedBatch[$key]['startDate']=date('d-m-Y',strtotime($startDate));
//                                 $recommendedBatch[$key]['endDate']=date('d-m-Y',strtotime($endDate));
//         						$batch_fecherd =$this->db_model->select_data('batch_specification_heading as batchSpecification, batch_fecherd as fecherd','batch_fecherd',array('batch_id'=>$value['id']));
//         						if(!empty($batch_fecherd)){
//         							   $recommendedBatch[$key]['batchFecherd'] =$batch_fecherd;
//         						}else{
//         							$recommendedBatch[$key]['batchFecherd']=array();
//         						}
//         					   // add payment type
//         					   $recommendedBatch[$key]['paymentType'] = $this->general_settings('payment_type');
//         					   $recommendedBatch[$key]['currencyCode'] = $this->general_settings('currency_code');
//         					   $recommendedBatch[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
        					   
//         					   $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$value['id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
//         					   if(!empty($batchSubject)){
//         					       foreach($batchSubject as $skey=>$svalue){
//         					            $cid=implode(', ',json_decode($svalue['chapter']));
//         					            $con ="id in ($cid)";
        					            
//         					            $chapter =$this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
//         					            if(!empty($chapter)){
//         					                foreach($chapter as $ckey=>$cvalue){
        					                    
//         					                    $sub_like = array('topic,batch',urldecode($cvalue['chapterName']).',"'.$value['id'].'"');
//                         					    $sub_videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$sub_like);
//                                                 if(!empty($sub_videos)){
//                                                     foreach($sub_videos as $vkey=>$vvalue){
//                                                         $url = $vvalue['url'];
//                                                         preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
//                                                         $sub_videos[$vkey]['videoId']=$match[1];
//                                                     }
//                                                     $chapter[$ckey]['videoLectures']=$sub_videos;
//                                                 }else{
//                                                   $chapter[$ckey]['videoLectures'] = array(); 
//                                                 }
        					                    
        					                    
//         					                }
//         					                $batchSubject[$skey]['chapter']=$chapter;
//         					            }else{
//         					             $batchSubject[$skey]['chapter']=array();   
//         					            }
//         					       }
        					       
//         					       $recommendedBatch[$key]['batchSubject'] = $batchSubject;
//         					   }else{
//         					     $recommendedBatch[$key]['batchSubject'] = array();  
//         					   }
//         					}
					        
// 					    }
					    
					    
// 					}
					
// 					$arr = array(
// 				                'status'=>'true',
// 				                'msg' =>$this->lang->line('ltr_fetch_successfully'),
// 				                'batchData'=>$batchData,
// 				                'yourBatch'=>$yourBatch,
// 				                'recommendedBatch'=>$recommendedBatch
// 				                );
					
// 				}else{
// 					$arr = array('status'=>'false', 'msg' => $this->lang->line('ltr_no_record_msg')); 
// 				}
// 			echo json_encode($arr);
// 		}

    function get_batch_fee(){
			$data = $_REQUEST;
 			$this->db_model->insert_data('temp_data',array('temp'=>json_encode($data)));
			$search = trim($data['search']);
		    $slider_limit = $data['limit'];
		    
			if(!empty($search)){
			  $like_search = array('batch_name',$search);
			}else{
			    $like_search = '';
			}
	
          if(isset($data['length']) && $data['length']>0){
                if(isset($data['start']) && !empty($data['start'])){
                    $limit = array($data['length'],$data['start']);
                    // $count = $data['start']+1;
                }else{ 
                    $limit = array($data['length'],0);
                    // $count = 1;
                }
            }else{
                $limit = '';
                // $count = 1;
            }
			
            $category = $this->db_model->select_data('id as categoryId,name as categoryName','batch_category use index (id)',array('status'=>1),$limit);
					     if(!empty($category)){
					        
					        foreach($category as $catkey=>$value){
					                 
					   //  }
					   
					   // $cond="id not in ($batch_id) AND status=1 AND cat_id= $value['id']";
					   $cond = array('status'=>1,'cat_id'=>$value['categoryId']);
					   
					    $subCategory = $this->db_model->select_data('id as SubcategoryId,name as SubcategoryName','batch_subcategory use index (id)',$cond,'');
					   // echo $this->db->last_query();
					    if(!empty($subCategory)){
					        
					        foreach($subCategory as $subkey=>$value){
					    
					     
					   $cond_sub1 = array('status'=>1,'sub_cat_id'=>$value['SubcategoryId']);
					   
	$batchData = $this->db_model->select_data('id, batch_name as batchName, start_date as startDate, end_date as endDate, start_time as startTime, end_time as endTime, batch_type as batchType, batch_price as batchPrice, no_of_student as noOfStudent ,status,description, batch_image as batchImage, batch_offer_price as batchOfferPrice','batches use index (id)',$cond_sub1,$slider_limit,array('id','desc'),$like_search);
       
				if(!empty($batchData)){
					foreach($batchData as $key=>$value){
						if(!empty($value['batchImage'])){
							$batchData[$key]['batchImage']= base_url('uploads/batch_image/').$value['batchImage'];
						}
						$startDate =$value['startDate'];
						$endDate =$value['endDate'];
                        $batchData[$key]['startDate']=date('d-m-Y',strtotime($startDate));
                        $batchData[$key]['endDate']=date('d-m-Y',strtotime($endDate));
                       
						$batch_fecherd =$this->db_model->select_data('batch_specification_heading as batchSpecification, batch_fecherd as fecherd','batch_fecherd',array('batch_id'=>$value['id']));
						if(!empty($batch_fecherd)){
							   $batchData[$key]['batchFecherd'] =$batch_fecherd;
						}else{
							$batchData[$key]['batchFecherd']=array();
						}
	
					   // add payment type
					   $batchData[$key]['paymentType'] = $this->general_settings('payment_type');
					   $batchData[$key]['currencyCode'] = $this->general_settings('currency_code');
					   $batchData[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
					   
					   
					   $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$value['id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
					   if(!empty($batchSubject)){
					       foreach($batchSubject as $skey=>$svalue){
					            $cid=implode(', ',json_decode($svalue['chapter']));
					            $con ="id in ($cid)";
					            $chapter =$this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
					            if(!empty($chapter)){
					                foreach($chapter as $ckey=>$cvalue){
					                    
					                    $sub_like = array('topic,batch',urldecode($cvalue['chapterName']).',"'.$value['id'].'"');
                					    $sub_videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$sub_like);
                                        if(!empty($sub_videos)){
                                            foreach($sub_videos as $vkey=>$vvalue){
                                                $url = $vvalue['url'];
                                                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                                $sub_videos[$vkey]['videoId']=$match[1];
                                            }
                                            $chapter[$ckey]['videoLectures']=$sub_videos;
                                        }else{
                                          $chapter[$ckey]['videoLectures'] = array(); 
                                        }
					                    
					                    
					                }
					                $batchSubject[$skey]['chapter']=$chapter;
					            }else{
					             $batchSubject[$skey]['chapter']=array();   
					            }
					            
					            
					       }
					       
					       $batchData[$key]['batchSubject'] = $batchSubject;
					   }else{
					     $batchData[$key]['batchSubject'] = array();  
					   }
					    $like = array('batch','"'.$value['id'].'"');
					    $videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$like);
                        if(!empty($videos)){
                            foreach($videos as $vkey=>$vvalue){
                                $url = $vvalue['url'];
                                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                $videos[$vkey]['videoId']=$match[1];
                            }
                            $batchData[$key]['videoLectures']=$videos;
                        }else{
                          $batchData[$key]['videoLectures'] = array(); 
                        }
					   $student_batch_dtail = $this->db_model->select_data('*','sudent_batchs',array('student_id'=>$data['student_id'],'batch_id' => $value['id'] ),'');
					   if(!empty($student_batch_dtail)){
					        $batchData[$key]['purchase_condition'] = true;
					   }else{
					       $batchData[$key]['purchase_condition'] = false;
					   }
					}
        				$subCategory[$subkey]['BatchData'] = $batchData;
        				
					    }else{
					        
					       unset($subCategory[$subkey]);
					       
					       
					       
        					   	}
        					   	
        					   	if(!empty($subCategory[$subkey]['BatchData'])){
        					   	     $subCategory=array_values($subCategory);  
        					   	$category[$catkey]['subcategory'] = $subCategory;
        					   	}else{
        					   	    unset($subCategory[$subkey]);
        					   	    unset($category[$catkey]['subcategory'][$subkey]);
        					   	}
        					   
        					   
							}
							if(!isset($category[$catkey]['subcategory'])){
							    unset($category[$catkey]);
							    
							}
							 
							 
					   }else{
					       
					   		$category[$catkey]['subcategory'] = array();
					   }
					  
					}
				}
					 $category=array_values($category); 
					 if($data['start']==0){
        				$getOtherBatch=$this->otherBatchData($data);
        				if($getOtherBatch){
        				    array_push($category,$getOtherBatch);
        				}
					 }

					$yourBatch=array();
					$recommendedBatch=array();
					
					if(!empty($data['student_id'])){
					    
					    $batchs_id =$this->db_model->select_data('batch_id','sudent_batchs',array('student_id'=>$data['student_id']));
					       //print_r($batchs_id);
					    if(!empty($batchs_id)){
					        
					        $batchId=array();
					        foreach($batchs_id as $key=>$value){
					            $batchId[$key] =$value['batch_id'];
					        }
				
					    $batch_id =implode(', ',$batchId);
					  
					    if(!empty($batch_id)){
					    $cond="id in ($batch_id)";
					    }else{
					        $cond = '';
					    }
					   
					    $yourBatch = $this->db_model->select_data('id ,batch_name as batchName, start_date as startDate, end_date as endDate, start_time	as startTime, end_time as endTime, batch_type as batchType, batch_price as batchPrice, no_of_student as noOfStudent ,status,description, batch_image as batchImage, batch_offer_price as batchOfferPrice','batches use index (id)',$cond,'',array('id','desc'));
					 
					                        
					    if(!empty($yourBatch)){
					        
					        foreach($yourBatch as $key=>$value){
        						if(!empty($value['batchImage'])){
        							$yourBatch[$key]['batchImage']= base_url('uploads/batch_image/').$value['batchImage'];
        						}
        						$startDate =$value['startDate'];
        						$endDate =$value['endDate'];
                                $yourBatch[$key]['startDate']=date('d-m-Y',strtotime($startDate));
                                $yourBatch[$key]['endDate']=date('d-m-Y',strtotime($endDate));
        						$batch_fecherd =$this->db_model->select_data('batch_specification_heading as batchSpecification, batch_fecherd as fecherd','batch_fecherd',array('batch_id'=>$value['id']));
        						if(!empty($batch_fecherd)){
        							   $yourBatch[$key]['batchFecherd'] =$batch_fecherd;
        						}else{
        							$yourBatch[$key]['batchFecherd']=array();
        						}
        					   // add payment type
        					   $yourBatch[$key]['paymentType'] = $this->general_settings('payment_type');
        					   $yourBatch[$key]['currencyCode'] = $this->general_settings('currency_code');
        					   $yourBatch[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
        					   
        					   $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$value['id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
        					   if(!empty($batchSubject)){
        					       foreach($batchSubject as $skey=>$svalue){
        					            $cid=implode(', ',json_decode($svalue['chapter']));
        					            $con ="id in ($cid)";
        					            
        					            $chapter =$this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
        					            if(!empty($chapter)){
        					                foreach($chapter as $ckey=>$cvalue){
        					                    
        					                    $sub_like = array('topic,batch',urldecode($cvalue['chapterName']).',"'.$value['id'].'"');
                        					    $sub_videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$sub_like);
                                                if(!empty($sub_videos)){
                                                    foreach($sub_videos as $vkey=>$vvalue){
                                                        $url = $vvalue['url'];
                                                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                                        $sub_videos[$vkey]['videoId']=$match[1];
                                                    }
                                                    $chapter[$ckey]['videoLectures']=$sub_videos;
                                                }else{
                                                  $chapter[$ckey]['videoLectures'] = array(); 
                                                }
        					                    
        					                    
        					                }
        					                $batchSubject[$skey]['chapter']=$chapter;
        					            }else{
        					             $batchSubject[$skey]['chapter']=array();   
        					            }
        					            
        					       }
        					       
        					       $yourBatch[$key]['batchSubject'] = $batchSubject;
        					   }else{
        					       $yourBatch[$key]['batchSubject'] = array();  
        					   }
        					    $like = array('batch','"'.$value['id'].'"');
        					    $videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$like);
                                if(!empty($videos)){
                                    foreach($videos as $vkey=>$vvalue){
                                        $url = $vvalue['url'];
                                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                        $videos[$vkey]['videoId']=$match[1];
                                    }
                                    $yourBatch[$key]['videoLectures']=$videos;
                                }else{
                                    $yourBatch[$key]['videoLectures'] = array(); 
                                }

        					}
					    }
					        
					    }
					    
					   $categoryData = $this->db_model->select_data('id as categoryId,name as categoryName','batch_category use index (id)',array('status'=>1),'');
					     if(!empty($categoryData)){
					        
					        foreach($categoryData as $key1=>$value){
					                 
					   //  }
					   
					   // $cond="id not in ($batch_id) AND status=1 AND cat_id= $value['id']";
					   $cond = array('status'=>1,'cat_id'=>$value['categoryId']);
					   

					    $subCategory = $this->db_model->select_data('id as SubcategoryId,name as SubcategoryName','batch_subcategory use index (id)',$cond,'');
					   // echo $this->db->last_query();
					    if(!empty($subCategory)){
					        
					        foreach($subCategory as $key2=>$value){
					    
					     
					   $cond_sub = array('status'=>1,'sub_cat_id'=>$value['SubcategoryId']);
					
                        $recommendedBatch = $this->db_model->select_data('id ,batch_name as batchName, start_date as startDate, end_date as endDate, start_time	as startTime, end_time as endTime, batch_type as batchType, batch_price as batchPrice, no_of_student as noOfStudent ,status,description, batch_image as batchImage, batch_offer_price as batchOfferPrice','batches use index (id)',$cond_sub,'',array('id','desc'));
                        // print_r($recommendedBatch);
                        // echo $this->db->last_query();
					    if(!empty($recommendedBatch)){
					        
					        foreach($recommendedBatch as $key=>$value){
        						if(!empty($value['batchImage'])){
        							$recommendedBatch[$key]['batchImage']= base_url('uploads/batch_image/').$value['batchImage'];
        						}
        						$startDate =$value['startDate'];
        						$endDate =$value['endDate'];
                                $recommendedBatch[$key]['startDate']=date('d-m-Y',strtotime($startDate));
                                $recommendedBatch[$key]['endDate']=date('d-m-Y',strtotime($endDate));
        						$batch_fecherd =$this->db_model->select_data('batch_specification_heading as batchSpecification, batch_fecherd as fecherd','batch_fecherd',array('batch_id'=>$value['id']));
        						if(!empty($batch_fecherd)){
        							   $recommendedBatch[$key]['batchFecherd'] =$batch_fecherd;
        						}else{
        							$recommendedBatch[$key]['batchFecherd']=array();
        						}
        					   // add payment type
        					   $recommendedBatch[$key]['paymentType'] = $this->general_settings('payment_type');
        					   $recommendedBatch[$key]['currencyCode'] = $this->general_settings('currency_code');
        					   $recommendedBatch[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
        					   
        					   $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$value['id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
        					   if(!empty($batchSubject)){
        					       foreach($batchSubject as $skey=>$svalue){
        					            $cid=implode(', ',json_decode($svalue['chapter']));
        					            $con ="id in ($cid)";
        					            
        					            $chapter =$this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
        					            if(!empty($chapter)){
        					                foreach($chapter as $ckey=>$cvalue){
        					                    
        					                    $sub_like = array('topic,batch',urldecode($cvalue['chapterName']).',"'.$value['id'].'"');
                        					    $sub_videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$sub_like);
                                                if(!empty($sub_videos)){
                                                    foreach($sub_videos as $vkey=>$vvalue){
                                                        $url = $vvalue['url'];
                                                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                                        $sub_videos[$vkey]['videoId']=$match[1];
                                                    }
                                                    $chapter[$ckey]['videoLectures']=$sub_videos;
                                                }else{
                                                  $chapter[$ckey]['videoLectures'] = array(); 
                                                }
        					                    
        					                    
        					                }
        					                $batchSubject[$skey]['chapter']=$chapter;
        					            }else{
        					             $batchSubject[$skey]['chapter']=array();   
        					            }
        					       }
        					       
        					       $recommendedBatch[$key]['batchSubject'] = $batchSubject;
        					   }else{
        					     $recommendedBatch[$key]['batchSubject'] = array();  
        					   }
        					   		

        					   	}
        					   	$subCategory[$key2]['BatchData'] = $recommendedBatch;
        					   	
        					}
        				
					       else{
        					   	 $subCategory[$key2]['BatchData'] = array();
        					   	 $categoryData[$key1]['subcategory'] = array();
        					   	}
							}
				// 			print_r($subCategory);
							 $categoryData[$key1]['subcategory'] = $subCategory;
					   }else{
					   		$categoryData[$key1]['subcategory'] = array();
					   }
					  
					}
				}
					    
					}
				
					$arr = array(
				                'status'=>'true',
				                'msg' =>$this->lang->line('ltr_fetch_successfully'),
				                'batchData'=>$category,
				                'yourBatch'=>$yourBatch,
				                // 'category'=>$categoryData,
				                'recommendedBatch'=>$categoryData
				                );
					
				// }else{
				// 	$arr = array('status'=>'false', 'msg' => $this->lang->line('ltr_no_record_msg')); 
				// }
			echo json_encode($arr);
		}
/*other batch function start*/
public function otherBatchData($data){
$search = trim($data['search']);
		    $slider_limit = $data['limit'];
		    
			if(!empty($search)){
			  $like_search = array('batch_name',$search);
			}else{
			    $like_search = '';
			}
	
          if(isset($data['length']) && $data['length']>0){
                if(isset($data['start']) && !empty($data['start'])){
                    $limit = array($data['length'],$data['start']);
                    // $count = $data['start']+1;
                }else{ 
                    $limit = array($data['length'],0);
                    // $count = 1;
                }
            }else{
                $limit = '';
                // $count = 1;
            }
					     $category=array('categoryId'=>'0','categoryName'=>'other');
					     $subCategory['SubcategoryId']='0';
					     $subCategory['SubcategoryName']='other';
					   $cond_sub1 = array('status'=>1,'sub_cat_id'=>0);
					   
	$batchData = $this->db_model->select_data('id, batch_name as batchName, start_date as startDate, end_date as endDate, start_time as startTime, end_time as endTime, batch_type as batchType, batch_price as batchPrice, no_of_student as noOfStudent ,status,description, batch_image as batchImage, batch_offer_price as batchOfferPrice','batches use index (id)',$cond_sub1,$slider_limit,array('id','desc'),$like_search);
       
				if(!empty($batchData)){
					foreach($batchData as $key=>$value){
						if(!empty($value['batchImage'])){
							$batchData[$key]['batchImage']= base_url('uploads/batch_image/').$value['batchImage'];
						}
						$startDate =$value['startDate'];
						$endDate =$value['endDate'];
                        $batchData[$key]['startDate']=date('d-m-Y',strtotime($startDate));
                        $batchData[$key]['endDate']=date('d-m-Y',strtotime($endDate));
                       
						$batch_fecherd =$this->db_model->select_data('batch_specification_heading as batchSpecification, batch_fecherd as fecherd','batch_fecherd',array('batch_id'=>$value['id']));
						if(!empty($batch_fecherd)){
							   $batchData[$key]['batchFecherd'] =$batch_fecherd;
						}else{
							$batchData[$key]['batchFecherd']=array();
						}
	
					   // add payment type
					   $batchData[$key]['paymentType'] = $this->general_settings('payment_type');
					   $batchData[$key]['currencyCode'] = $this->general_settings('currency_code');
					   $batchData[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
					   
					   
					   $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$value['id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
					   if(!empty($batchSubject)){
					       foreach($batchSubject as $skey=>$svalue){
					            $cid=implode(', ',json_decode($svalue['chapter']));
                                if(!empty($cid)){
					            $con ="id in ($cid)";
					            $chapter =$this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
					            if(!empty($chapter)){
					                foreach($chapter as $ckey=>$cvalue){
					                    
					                    $sub_like = array('topic,batch',urldecode($cvalue['chapterName']).',"'.$value['id'].'"');
                					    $sub_videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$sub_like);
                                        if(!empty($sub_videos)){
                                            foreach($sub_videos as $vkey=>$vvalue){
                                                $url = $vvalue['url'];
                                                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                                $sub_videos[$vkey]['videoId']=$match[1];
                                            }
                                            $chapter[$ckey]['videoLectures']=$sub_videos;
                                        }else{
                                          $chapter[$ckey]['videoLectures'] = array(); 
                                        }
					                    
					                    
					                }
					                $batchSubject[$skey]['chapter']=$chapter;
					            }else{
					             $batchSubject[$skey]['chapter']=array();   
					            }
                            }else{
                                $batchSubject[$skey]['chapter']=array();   
                               }
					            
					            
					       }
					       
					       $batchData[$key]['batchSubject'] = $batchSubject;
					   }else{
					     $batchData[$key]['batchSubject'] = array();  
					   }
					    $like = array('batch','"'.$value['id'].'"');
					    $videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$like);
                        if(!empty($videos)){
                            foreach($videos as $vkey=>$vvalue){
                                $url = $vvalue['url'];
                                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                $videos[$vkey]['videoId']=$match[1];
                            }
                            $batchData[$key]['videoLectures']=$videos;
                        }else{
                          $batchData[$key]['videoLectures'] = array(); 
                        }
					   $student_batch_dtail = $this->db_model->select_data('*','sudent_batchs',array('student_id'=>$data['student_id'],'batch_id' => $value['id'] ),'');
					   if(!empty($student_batch_dtail)){
					        $batchData[$key]['purchase_condition'] = true;
					   }else{
					       $batchData[$key]['purchase_condition'] = false;
					   }
					}
        				$subCategory['BatchData'] = $batchData;
        				
					    }else{
					        
					       unset($subCategory);
					       
					       
					       
        					   	}
        					   	
        					   	if(!empty($subCategory['BatchData'])){
        					   	     //$subCategory=array_values($subCategory);  
        					   	$category['subcategory'] = array($subCategory);
        					   	}else{
        					   	    unset($subCategory);
        					   	    unset($category['subcategory']);
        					   	}
        					   if(!empty($category['subcategory'])){
        					       return	$category;
        					   }else{
        					       return	false;
        					   }
        					   //print_r($category);
						
}
							/*Other batch functio end*/
    function profile_update(){
        $data = $_REQUEST;
        if(isset($data['name']) && isset($data['password']) && isset($data['student_id'])){
            $data_arr = [];
            $path = 'uploads/students/';
            if($data['name'] != ''){
                $data_arr['name'] = $data['name'];
            }
            $pimage = $this->db_model->select_data('image','students use index (id)',array('id'=>$data['student_id']),1)[0]['image'];         
            if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
                $image = $this->upload_media($_FILES,$path,'image');
                if(is_array($image)){
                    $arr = array(
                        'status'=>'false',
                        'msg' => $image['msg'],
                    );
                }else{
                    $data_arr['image'] = $image;
                    $pimage = $image;
                }
            }
            if($data['password'] != ''){
                $data_arr['password'] = md5($data['password']);
            }
            if(!empty($data_arr)){
                $ins = $this->db_model->update_data_limit('students',$data_arr,array('id'=>$data['student_id']));  
                if($ins==true){
                    $arr = array(
                        'status'=>'true',
                        'name'=>$data['name'],
                        'image'=>base_url('uploads/students/').$pimage,
                        'msg'=>$this->lang->line('ltr_profile_updated_msg')
                    );
                }else{
                    $arr = array(
                        'status'=>'false'
                    );
                }
            }else{
                $arr = array(
                    'status'=>'false',
                    'msg'=>$this->lang->line('ltr_name_can_msg')
                ); 
            } 
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
    function upload_media($files,$path,$file){   
        $config['upload_path'] =$path;
        $config['allowed_types'] = 'jpeg|jpg|png';
        $config['max_size']    = '0';
        $filename = '';		
        $this->load->library('upload', $config);
        if ($this->upload->do_upload($file)){
            $uploadData = $this->upload->data();
            $filename = $uploadData['file_name'];
            return $filename;
        }else{
            $resp = array('status'=>'2', 'msg' => $this->upload->display_errors());
            return $resp;
        }     
    }
    function notice_count(){
        $data = $_REQUEST;
        if(isset($data['admin_id'])){
            $admin_id = $data['admin_id'];
            $unreadNotice=[];
            $cond = "admin_id = $admin_id AND status = 1 AND (notice_for = 'Student' OR notice_for = 'Both')";
            $notice_count = $this->db_model->countAll('notices',$cond);
            if(!empty($notice_count)){
                $arr = array('status'=>'true','unreadNotice'=>$notice_count);
            }else{
                $arr = array('status'=>'false','msg'=>$this->lang->line('ltr_something_msg'));
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            );
        }
		echo json_encode($arr);
    }
    function notices(){
        $data = $_REQUEST;
        if(isset($data['admin_id']) && isset($data['student_id'])){
            $admin_id = $data['admin_id'];
            if(!empty($data['uid'])){
                $this->viewNotificationStatus($data['uid'],'notices');
            }
            $student_id = $data['student_id'];
            $cond = "admin_id = $admin_id AND status = 1";
            if($student_id == 'all'){
                $cond .= " AND (notice_for = 'Student' OR notice_for = 'Both')";
            }else{
                $cond .= " AND student_id = $student_id";
            }
            // $cond = "admin_id = $admin_id AND status = 1 AND (notice_for = 'Student' OR notice_for = 'Both')";
            $notices = $this->db_model->select_data('`id`, `title`, `description`, `notice_for` as noticeFor, `status`, `date`, `admin_id` as adminId, `student_id` as studentId, `teacher_id` as teacherId, `added_by` as addedBy, `read_status` as readStatus, `added_at` as addedAt','notices use index (id)',$cond,'',array('id','desc'));
            if($notices){
                foreach($notices as $key=>$value){
                    
                    $notices[$key]['date']= date('d-m-Y',strtotime($value['date']));
                    $notices[$key]['addedAt']= date('d-m-Y',strtotime($value['addedAt']));
                }
                $this->db_model->update_data('notices use index(id)',array('read_status'=>1),array('student_id'=>$student_id));
                $arr = array('status'=>'true','msg'=>$this->lang->line('ltr_fetch_successfully'),'allNotice'=>$notices);
            }else{
                $arr = array('status'=>'false','msg'=>$this->lang->line('ltr_no_record_msg'));
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            );
        }
    	echo json_encode($arr);		
    }
    function extraClass(){
        $data = $_REQUEST;
        if(isset($data['admin_id']) && isset($data['type']) && isset($data['page_no']) && isset($data['batch_id'])){
            if(!empty($data['student_id'])){
                $this->viewNotificationStatus($data['student_id'],'extraClass');
            }
            $admin_id = $data['admin_id'];
            $type = $data['type'];
            $startlimit = $data['page_no'];
            $batch_id_like = '"'.$data['batch_id'].'"';
            if($startlimit > 0){
                $start = ((($startlimit)*10)+1)-1; 
            }else{
                $start = 0;
            }
            $limit = array(10,$start);
            if($type=='previous'){
                $cond = array('extra_classes.admin_id'=>$admin_id,'extra_classes.date < '=>date('Y-m-d'));
                $order = array('date','desc');
            }else{
                $cond = array('extra_classes.admin_id'=>$admin_id,'extra_classes.date >= '=>date('Y-m-d'));
                $order = array('date','asc');
            }
            $extraClsData = $this->db_model->select_data(' extra_classes.id,extra_classes.admin_id as adminId, extra_classes.date,extra_classes.start_time as startTime, extra_classes.end_time as endTime,extra_classes.teacher_id as teacherId, extra_classes.description,extra_classes.status,extra_classes.batch_id as batchId,extra_classes.added_at as addedAt,extra_classes.completed_date_time as completedDateTime,users.name,users.teach_gender as teachGender','extra_classes use index (id)',$cond,$limit,$order,array('batch_id',$batch_id_like),array('users','users.id = extra_classes.teacher_id'),'');
            if (!empty($extraClsData)){
                $arr = array(
                    'extraClass' => $extraClsData,
                    'status' => 'true',
                    'msg' => $this->lang->line('ltr_fetch_successfully')
                );
            } else {
                $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                );
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            );
        }
        echo json_encode($arr);
    }
    function Homework(){ 
        $data = $_REQUEST;
        if(isset($data['admin_id']) && isset($data['batch_id']) && isset($data['homework_date'])){
            
            if(!empty($data['student_id'])){
                $this->viewNotificationStatus($data['student_id'],'homeWork');
            }
            $admin_id = $data['admin_id'];
            $batch_id = $data['batch_id'];
            $date = date('Y-m-d',strtotime($data['homework_date'])); 
            $cond = array('homeworks.admin_id'=>$admin_id,'homeworks.date'=>date('Y-m-d',strtotime($date)),'homeworks.batch_id'=>$batch_id);
            $homewrkData = $this->db_model->select_data('homeworks.id,homeworks.admin_id as adminId,homeworks.teacher_id teacherId, homeworks.date,homeworks.subject_id as studentId, homeworks.batch_id as batchId,homeworks.description,homeworks.added_at as addedAt,users.name,users.teach_gender as teachGender,subjects.subject_name as subjectName','homeworks use index (id)',$cond,'',array('id','desc'),'',array('multiple',array(array('users','users.id = homeworks.teacher_id'),array('subjects','subjects.id = homeworks.subject_id'))),'');
            if (!empty($homewrkData)) {
                $arr = array(
                    'homeWork' => $homewrkData,
                    'status' => 'true',
                    'msg' => $this->lang->line('ltr_fetch_successfully')
                );
            }else{
                $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                );
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            );
        }
        echo json_encode($arr);
    }
    function get_subject(){
        $data = $_REQUEST;
        if(isset($data['admin_id']) && isset($data['batch_id'])){
            if(!empty($data['student_id'])){
                $this->viewNotificationStatus($data['student_id'],'videoLecture');
            }
            $subject = $this->db_model->select_data('subjects.id,subject_name as subjectName','subjects use index (id)',array('subjects.admin_id'=>$data['admin_id'],'batch_subjects.batch_id'=>$data['batch_id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
            
            $arr = array(
                'subject' => $subject,
                'status' => 'true',
                'msg' => $this->lang->line('ltr_fetch_successfully')
            ); 
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
    function get_chapter(){
        $data = $_REQUEST;
        if(isset($data['subject_id'])){
            $chapter = $this->db_model->select_data('chapter_name  as chapterName','chapters use index (id)',array('subject_id'=>$data['subject_id']),'',array('id','desc'));
            if(!empty($chapter)){
                $arr = array(
                    'chapter' => $chapter,
                    'status' => 'true',
                    'msg' => $this->lang->line('ltr_fetch_successfully')
                ); 
            }else{
                $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                ); 
            }
            
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
    function video_lecture(){
        $data = $_REQUEST;
        if(isset($data['admin_id']) && isset($data['page_no']) && $data['batch_id']){
            $startlimit = $data['page_no'];
            $admin_id = $data['admin_id'];
            if($startlimit > 1){
                $start = (($startlimit-1)*10)+1; 
            }else{
                $start = 0;
            }
            $limit = array(10,$start);
            $like = array('batch','"'.$data['batch_id'].'"');
            if(isset($data['chapter']) && $data['chapter']!=''){
                $like = array('topic,batch',urldecode($data['chapter']).',"'.$data['batch_id'].'"');
            }
            $videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('admin_id'=>$admin_id),$limit,array('id','desc'),$like);
            if(!empty($videos)){
                foreach($videos as $key=>$value){
                    $url = $value['url'];
                    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                    $videos[$key]['videoId']=$match[1];
                }
            }
           
            $num_rows = $this->db_model->countAll('video_lectures use index (id)',array('admin_id'=>$admin_id));
            if(!empty($videos)){
                $arr = array(
                    'videoLecture' => $videos,
                    'totalCount' => $num_rows,
                    'encCode' => base64_encode('AIzaSyAQ8wpz16d6izBb0wfpDI1A895Bln5nbRs'),
                    'status' => 'true',
                    'msg' => $this->lang->line('ltr_fetch_successfully')
                );
            }else{
                $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                );
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
    function viewVacancy()
    {   
        $data = $_REQUEST;
        if(isset($data['admin_id'])){
            
            if(!empty($data['student_id'])){
                $this->viewNotificationStatus($data['student_id'],'vacancy');
             }
            $currentDate = date("Y-m-d");
            $admin_id  =  $data['admin_id'];
            $vacancy = $this->db_model->select_data('id,title,description,start_date as startDate,last_date as lastDate,mode,files,status,admin_id as adminId,added_at as addedAt','vacancy use index (id)',array('admin_id'=>$admin_id,'last_date >= '=> $currentDate,'status'=>1),'',array('id','desc'));
            if (!empty($vacancy)){
                foreach($vacancy as $key=>$value){
                    $vacancy[$key]['startDate']= date('d-m-Y', strtotime($value['startDate']));
                    $vacancy[$key]['lastDate']= date('d-m-Y', strtotime($value['lastDate']));
                    $vacancy[$key]['addedAt']= date('d-m-Y', strtotime($value['addedAt']));
                }
                $arr = array(
                    'vacancy' => $vacancy,
                    'filesUrl' => base_url('uploads/vacancy/'),
                    'status' => 'true',
                    'msg' => $this->lang->line('ltr_fetch_successfully'),
                );
            }else{
                $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                );
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
 	function app_version(){
        $appVersion = $this->db_model->select_data('*','app_versions');
 	    if(!empty($appVersion)){
			$arr = array( 
        				'status'=>'true',
        				'version'=>$appVersion[0]['latest_version'],
        			);
		}else{
			$arr = array('status'=>'false');
		}
		echo json_encode($arr);
 	}
 	function update_version(){
        $data =	$_REQUEST;
        if(isset($data['version'])){
            $ins = $this->db_model->update_data_limit('app_versions',array('latest_version'=>$data['version']),array('id'=>1),1);
            if($ins){
               $arr = array(
                   'status' => 'true',
                   'msg' => $this->lang->line('ltr_appv_updated_msg')
               );
           }else{
               $arr = array('status'=>'false');
           }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
		echo json_encode($arr);
     }
    function last_login_time(){
        $data =	$_REQUEST;
        if(isset($data['student_id'])){
            $ins = $this->db_model->update_data_limit('students',array('last_login_app'=>date('Y-m-d H:i:s')),array('id'=>$data['student_id']),1);
            if($ins){
               $arr = array(
                   'status' => 'true',
               );
           }else{
               $arr = array('status'=>'false');
           }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
    function db_new_changes(){
        $data =	$_REQUEST;
        if(isset($data['admin_id']) && isset($data['student_id']) && isset($data['batch_id'])){
            $admin_id = $data['admin_id'];
            $student_id = $data['student_id'];
            $batch_id = $data['batch_id'];
            
            $last_login = $this->db_model->select_data('last_login_app','students use index(id)',array('id'=>$student_id),1);
            
            if($last_login[0]['last_login_app'] != '0000-00-00 00:00:00'){
                
                $lastLoginTime = $last_login[0]['last_login_app'];
                
                $likeEX = array('batch_id','"'.$batch_id.'"');
                $extraData = $this->db_model->select_data('extra_classes.id,extra_classes.admin_id as adminId,extra_classes.date, extra_classes.start_time as startDate,extra_classes.end_time as endTime,extra_classes.teacher_id as teacherId,extra_classes.description,extra_classes.status,extra_classes.batch_id as batchId,extra_classes.added_at as addedAt,extra_classes.completed_date_time as completedDateTime,users.name,users.teach_gender as teachGender','extra_classes use index(id)',array('extra_classes.admin_id'=>$admin_id,'extra_classes.added_at >= '=>$lastLoginTime,'date >=' => date('Y-m-d')),'',array('date','asc'),$likeEX,array('users','users.id = extra_classes.teacher_id'));
                $extraClass =array();
                if(!empty($extraData)){
                    foreach($extraData as $key=>$value){
                        $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'extraClass'));
                        if(empty($view_n)){
                            $extraClass[$key]=$value;
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $extraClass[$key]=$value;
                            }
                        }
                    }
                }
                
                $homewrk = $this->db_model->select_data('homeworks.id, homeworks.admin_id as adminId,homeworks.teacher_id as teacherId, homeworks.date,homeworks.subject_id as subjectId, homeworks.batch_id as batchId, homeworks.description,homeworks.added_at as addedAt,users.name,users.teach_gender as teachGender,subjects.subject_name as subjectName','homeworks use index (id)',array('homeworks.admin_id'=>$admin_id,'homeworks.added_at >= '=>$lastLoginTime,'homeworks.batch_id'=>$batch_id,'date >=' => date('Y-m-d')),'',array('id','desc'),'',array('multiple',array(array('users','users.id = homeworks.teacher_id'),array('subjects','subjects.id = homeworks.subject_id'))),'');
                $homewrkData=array();
                if(!empty($homewrk)){
                    foreach($homewrk as $key=>$value){
                     $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'homeWork'));
                        if(empty($view_n)){
                            $homewrkData[$key]=$value;
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $homewrkData[$key]=$value;
                            }
                        }
                    }
                }
                
                $likev = array('batch','"'.$batch_id.'"');
                $videosData = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt, video_type as videoType','video_lectures use index (id)',array('admin_id'=>$admin_id,'added_at >= '=>$lastLoginTime),'',array('id','desc'),$likev);
                $videos =array();
               
                if(!empty($videosData)){
                    foreach($videosData as $key=>$value){
                        $sub_id =$this->db_model->select_data('subject_id','batch_subjects',array('batch_id'=>$batch_id),1);
                        if(!empty($sub_id)){
                            $sub_name =$this->db_model->select_data('subject_name','subjects',array('id'=>$sub_id[0]['subject_id']),1);
                            if(!empty($sub_name)){
                               // if($sub_name[0]['subject_name']==$value['subject']){
                                    $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id'=>$student_id,'notice_type'=>'videoLecture'));
                                    if(empty($view_n)){
                                        $videos[$key]=$value;
                                        $url = $value['url'];
                                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                        $videos[$key]['videoId']=$match[1];
                                    }else{
                                        if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                            $videos[$key]=$value;
                                            $url = $value['url'];
                                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                        $videos[$key]['videoId']=$match[1];
                                        }
                                    }
                            //  }
                            }
                        }
                        
                    }
                }
                
                $vacancyData = $this->db_model->select_data('id,title,description,start_date as startDate,last_date as lastDate,mode,files,status,admin_id as adminId,added_at as addedAt','vacancy use index (id)',array('admin_id'=>$admin_id,'last_date >= '=> date("Y-m-d"),'status'=>1,'added_at >='=>$lastLoginTime),'',array('id','desc'));
                $vacancy=array();
                if(!empty($vacancyData)){
                    foreach($vacancyData as $key=>$value){
                        $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'vacancy'));
                        if(empty($view_n)){
                            $vacancy[$key]=$value;
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $vacancy[$key]=$value;
                            }
                        }
                    }
                }
                
                $noticesData = $this->db_model->select_data('`id`, `title`, `description`, `notice_for` as noticeFor, `status`, `date`, `admin_id` as adminId, `student_id` as studentId, `teacher_id` as teacherId, `added_at` as addedAt, `added_by` as addedBy, `read_status` as readStatus','notices use index (id)',"admin_id = $admin_id AND status = 1 AND (notice_for = 'Student' OR notice_for = 'Both' OR student_id = $student_id) AND added_at >= '$lastLoginTime' AND date >= CURDATE()",'',array('id','desc'));
                
                $notices=array();
                if(!empty($noticesData)){
                    foreach($noticesData as $key=>$value){
                        $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id'=>$student_id,'notice_type'=>'notices'));
                        if(empty($view_n)){
                            $notices[$key]=$value;
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $notices[$key]=$value;
                            }
                        }
                    }
                }
                
                $practice_paper = $this->db_model->select_data('id,name,time_duration as timeDuration, added_at as addedAt, total_question as totalQuestion','exams use index (id)',array('type'=>2,'admin_id'=>$admin_id,'status'=>1,'batch_id'=>$batch_id,'added_at >='=>$lastLoginTime),'',array('id','desc'));
                
                 $newexampaper_test = array();       
                if(!empty($practice_paper)){
                    foreach($practice_paper as $key=>$pexam){
                            $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id'=>$student_id,'notice_type'=>'practicePaper'));
                            $paperstatus = $this->db_model->select_data('id','practice_result use index (id)',array('admin_id' => $admin_id,'student_id' => $student_id,'paper_id' => $pexam['id']),1);
                         
                            if(empty($paperstatus) && empty($view_n)){
                               // array_push($newexampaper_test,$pexam); 
                               $newexampaper_test[$key]=$pexam;
                            }else{
                                if(!empty($view_n)){
                        
                                    if(strtotime($view_n[0]['views_time'])<strtotime($pexam['addedAt'])){
                                        $newexampaper_test[$key]=$pexam;
                                        
                                      }
                                  
                                }
                            }
                        }
                    }
                    
                    
                $mock_paper = $this->db_model->select_data('id,name,time_duration as timeDuration, added_at as addedAt, total_question as  totalQuestion,mock_sheduled_date as mockSheduledDate,mock_sheduled_time as mockSheduledTime','exams use index (id)',array('type' => 1,'admin_id' => $admin_id,'status' => 1,'batch_id' => $batch_id,'mock_sheduled_date >=' => date('Y-m-d'),'added_at >='=>$lastLoginTime),'',array('id','desc'));
               
                $newexampaper = array();          
                if(!empty($mock_paper)){
                    foreach($mock_paper as $exam){
                            $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'mockPaper'));
                            $paperstatus = $this->db_model->select_data('id','mock_result use index (id)',array('admin_id' => $admin_id,'student_id' => $student_id,'paper_id' => $exam['id']),1);
                            if($exam['mockSheduledDate']==date('Y-m-d')){
                                if(strtotime($exam['mockSheduledTime'])>strtotime(date('H:i:s'))){
                                    if(empty($paperstatus) && empty($view_n)){
                                array_push($newexampaper,$exam); 
                                    }else{
                                        if(!empty($view_n)){
                                            if(strtotime($view_n[0]['views_time'])<strtotime($exam['addedAt'])){
                                                array_push($newexampaper,$exam);
                                              }
                                          
                                        }
                                    }
                                }
                            }else{
                                if(empty($paperstatus) && empty($view_n)){
                                array_push($newexampaper,$exam); 
                                    }else{
                                        if(!empty($view_n)){
                                            if(strtotime($view_n[0]['views_time'])<strtotime($exam['addedAt'])){
                                                array_push($newexampaper,$exam);
                                              }
                                          
                                        }
                                    }
                            }
                         
                            
                        }
                    }
                    
                $addNewBook=array();
                $like = array('batch','"'.$batch_id.'"');
    		    $book_pdf =$this->db_model->select_data('id,admin_id as adminId, title, batch, topic, subject, file_name as fileName,  status, added_by as addedBy, added_at as addedAt','book_pdf use index (id)',array('status'=>1,'added_at >= '=>$lastLoginTime),'',array('id','desc'),$like);
    		    if(!empty($book_pdf)){
    		        foreach($book_pdf as $key=>$value){
    		            $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'book_notes_paper'));
                        if(empty($view_n)){
                            $addNewBook[$key]=$value;
                            $addNewBook[$key]['url']= base_url('uploads/book/');
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $addNewBook[$key]=$value;
                                $addNewBook[$key]['url']= base_url('uploads/book/');
                            }
                        }
    		        }
    		    }
    		    
    		    $addNewNotes=array();
    		    $notes_pdf =$this->db_model->select_data('id,admin_id as adminId, title, batch, topic, subject, file_name as fileName,  status, added_by as addedBy, added_at as addedAt','notes_pdf use index (id)',array('status'=>1,'added_at >= '=>$lastLoginTime),'',array('id','desc'),$like);
    		    if(!empty($notes_pdf)){
    		        foreach($notes_pdf as $key=>$value){
    		            
    		            $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'book_notes_paper'));
                        if(empty($view_n)){
                            $addNewNotes[$key]=$value;
                            $addNewNotes[$key]['url']= base_url('uploads/notes/');
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $addNewNotes[$key]=$value;
                                $addNewNotes[$key]['url']= base_url('uploads/notes/');
                            }
                        }
    		        }
    		    }
    		    
    		    $addOldPaper=array();
                $newexampaper =$this->db_model->select_data('id,admin_id as adminId, title, batch, topic, subject, file_name as fileName,  status, added_by as addedBy, added_at as addedAt','old_paper_pdf use index (id)',array('status'=>1,'added_at >= '=>$lastLoginTime),'',array('id','desc'),$like);
    		    if(!empty($newexampaper)){
    		        foreach($newexampaper as $key=>$value){
    		            
    		            $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'book_notes_paper'));
                        if(empty($view_n)){
                            $addOldPaper[$key]=$value;
                            $addOldPaper[$key]['url']= base_url('uploads/oldpaper/');
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $addOldPaper[$key]=$value;
                                $addOldPaper[$key]['url']= base_url('uploads/oldpaper/');
                            }
                        }
    		        }
    		    }
                
                $arr = array(
                    'status'=>'true',
                    'extraClass'=> $extraClass,
                    'homeWork'=>$homewrkData,
                    'videoLecture'=>$videos,
                    'vacancy'=>$vacancy,
                    'notices'=>$notices,
                    'practicePaper'=>$newexampaper_test,
                    'mockPaper'=>$newexampaper,
                    'addOldPaper'=>$addOldPaper,
                    'addNewBook'=>$addNewBook,
                    'addNewNotes'=>$addNewNotes,
                ); 
                
            }else{
                $arr = array('status'=>'false','msg'=>$this->lang->line('ltr_its_first_login'));
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
    
    function viewNotificationStatus($student_id='',$notice_type=''){
        $data = $_REQUEST;
        if(!empty($student_id) && !empty($notice_type)){
            
           
             $data_arr=array();
             $cu_date = date('Y-m-d H:i:s');
             $data_arr['student_id']=$student_id;
             $data_arr['notice_type']=$notice_type;
             $noticeD = $this->db_model->select_data('*','views_notification_student',array('student_id'=>$student_id,'notice_type'=>$notice_type),1);
             if(!empty($noticeD)){
                 $this->db_model->update_data_limit('views_notification_student ',array('views_time'=>$cu_date),array('n_id'=>$noticeD[0]['n_id']),1);
             }else{
                 $data_arr = $this->security->xss_clean($data_arr);
                 $ins = $this->db_model->insert_data('views_notification_student',$data_arr);
                
             }
        }
                
            return;
            
    }
    
    function getLiveClassData()
    {   
        $data = $_REQUEST;
        if(isset($data['batch_id'])){
           
            $setting = $this->db_model->select_data('meeting_number as meetingNumber,password','live_class_setting',array('batch'=>$data['batch_id'],'status'=>1),'',array('id','desc'));
            $datasdk = $this->db_model->select_data('android_api_key,android_api_secret','zoom_api_credentials');
            $setting[0]['sdkKey']=$datasdk[0]['android_api_key'];
            $setting[0]['sdkSecret']=$datasdk[0]['android_api_secret'];
            if (!empty($setting)){
                $arr = array(
                    'data' => $setting,
                    'status' => 'true',
                    'msg' => $this->lang->line('ltr_fetch_successfully'),
                );
            }else{
                $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                );
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
    function getAttendance(){ 
        $data = $_REQUEST;
        if(isset($data['student_id']) && isset($data['month']) && isset($data['year'])){
            $student_id = $data['student_id'];
            $year=$data['year'];
            $month=$data['month'];
            $like = array('date',$year.'-'.$month);
            $attendance = $this->db_model->select_data('student_id as studentId,added_id as addedId,date,time','attendance use index (id)',array('student_id'=>$student_id),'','',$like,'','');
            if (!empty($attendance)) {
                $arr = array(
                    'attendance' => $attendance,
                    'status' => 'true',
                    'msg' => $this->lang->line('ltr_fetch_successfully')
                );
            }else{
                $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                );
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            );
        }
        echo json_encode($arr);
    }
    
    function getTopScorer(){ 
        $data = $_REQUEST;
        if(isset($data['batch_id'])){
            $batch_id = $data['batch_id'];
            $exam = $this->db_model->select_data('id,name','exams  use index (id)',array('batch_id'=>$batch_id,'type'=>1,'mock_sheduled_date <='=>date('Y-m-d')),'1',array('id','desc'));
            $top_three = $this->db_model->select_data('mock_result.paper_name as paperName,students.name,students.image,mock_result.percentage','mock_result  use index (id)',array('paper_id'=>$exam[0]['id'],'mock_result.percentage >'=>0),'3',array('mock_result.percentage','desc'),'',array('students','students.id=mock_result.student_id'));
            if (!empty($top_three)) {
                $arr = array(
                    'topThree' => $top_three,
                    'filesUrl' => base_url('uploads/students/'),
                    'status' => 'true',
                    
                    'msg' => $this->lang->line('ltr_fetch_successfully')
                );
            }else{
                $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                );
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            );
        }
        echo json_encode($arr);
    }
    
    function getAcademicRecord(){ 
        $data = $_REQUEST;
        if(isset($data['student_id']) && isset($data['month']) && isset($data['year']) && isset($data['batch_id'])){
            $student_id = $data['student_id'];
            $year=$data['year'];
            $month=$data['month'];
            $like = $year.'-'.$month.'-';
            $batch_id=$data['batch_id'];
            $like_batch_id='"'.$data['batch_id'].'"';
		    $data1['extraClass'] = $this->db_model->countAll('extra_class_attendance',array('student_id'=>$student_id),'','',array('date',$like));
		    $data1['totalExtraClass'] = $this->db_model->countAll('extra_classes','',array('batch_id'=>$like_batch_id),'',array('date',$like));
    	    
    		$data1['practiceResult'] =(int)$this->db_model->custom_slect_query(" COUNT(*) AS `numrows` FROM ( SELECT practice_result.id FROM `practice_result` JOIN `exams` ON `exams`.`id`=`practice_result`.`paper_id` WHERE  `student_id` = '".$student_id."' AND date(added_at) LIKE '%".$like."%' ESCAPE '!' GROUP BY `paper_id` ) a")[0]['numrows'];
    		
    		$data1['totalPracticeTest'] = $this->db_model->countAll('exams',array('batch_id'=>$batch_id,'type'=>2),'','',array('date(added_at)',$like));
    		
		    $data1['mockResult'] = $this->db_model->countAll('mock_result',array('student_id'=>$student_id),'','',array('date',$like));
		    
		    $data1['totalMockTest'] = $this->db_model->countAll('exams',array('batch_id'=>$batch_id,'type'=>1),'','',array('date(added_at)',$like));
		  
            if (!empty($data1)) {
                $arr = array(
                    'academicRecord' => $data1,
                    'status' => 'true',
                    'msg' => $this->lang->line('ltr_fetch_successfully')
                );
            }else{
                $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                );
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            );
        }
        echo json_encode($arr);
    }
    
    
    function checkActiveLiveClass(){ 
        $data = $_REQUEST;
        if(isset($data['batch_id'])){
            $batch_id = $data['batch_id'];
		    $class_data = $this->db_model->select_data('users.name,users.teach_image AS teachImage,subjects.subject_name as subjectName,chapters.chapter_name as chapterName,live_class_history.end_time as endTime','live_class_history',array('batch_id'=>$batch_id),'1',array('live_class_history.id','desc'),'',array('multiple',array(array('users','users.id = live_class_history.uid'),array('subjects','subjects.id = live_class_history.subject_id'),array('chapters','chapters.id = live_class_history.chapter_id'))));
		   
            if (!empty($class_data)) {
                if(empty($class_data[0]['endTime'])){
                    $arr = array(
                    'liveClass' => $class_data[0],
                    'filesUrl' => base_url('uploads/teachers/'),
                    'status' => 'true',
                    'msg' => $this->lang->line('ltr_fetch_successfully')
                );
                }else{
                     $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                );
                }
                
            }else{
                $arr = array(
                    'status' => 'false',
                    'msg' => $this->lang->line('ltr_no_record_msg')
                );
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            );
        }
        echo json_encode($arr);
    }
    // function certificate(){
      
    //    $data = $_REQUEST;
    //     if(isset($data['batch_id']) && isset($data['student_id']) ){
    //         $id=$data['student_id'];
    //     	$batch_id = $data['batch_id'];
    //     	$data['student_certificate']=$this->db_model->select_data('*','certificate',array('student_id'=>$id,'batch_id'=>$batch_id),1,array('id','desc'));
    //     	if(!empty($data['student_certificate'])){
    //     	    $data['certificate_details']=$this->db_model->select_data('*','certificate_setting','',1,array('id','desc'));
    //         	$data['site_details_logo']=$this->db_model->select_data('site_logo','site_details','',1,array('id','desc'));
    //         	$data['student_details']=$this->db_model->select_data('name','students',array('id'=>$id),1,array('id','desc'));
    //         	$data['batchdata']=$this->db_model->select_data('batch_name','batches',array('id'=>$batch_id),1,array('id','desc'));
    //         	$data['baseurl'] = base_url();
    //             $html=	$this->load->view("student/certificate_pdf",$data,true); 
    //             $this->load->library('pdf'); // change to pdf_ssl for ssl
    //             $filename = "certificate_".$id."_".$batch_id;
    //             $result=$this->pdf->create($html);
                
    //             $file_path= explode("application",APPPATH);
    //             file_put_contents($file_path[0].'uploads/certificate/'.$filename.'.pdf', $result);
    //             $arr = array(
    //                 'fileName' => $filename.'.pdf',
    //                 'filesUrl' => base_url('uploads/certificate/'),
    //                 'status' => 'true',
    //                 'msg' => $this->lang->line('ltr_fetch_successfully')
    //             );
    //         }else{
    //             $arr = array(
    //                 'status' => 'false',
    //                 'msg' => $this->lang->line('ltr_no_record_msg')
    //             );
    //         }
    //     }else{
    //         $arr = array(
    //             'status'=>'false',
    //             'msg'=>$this->lang->line('ltr_missing_parameters_msg')
    //         );
    //     }
    //     echo json_encode($arr);
    // }
    
    function apply_leave(){
        $data = $_REQUEST;
            if(!empty($data['from_date']) && !empty($data['to_date']) && !empty($data['subject']) && !empty($data['uid'])){
        
                $data_arr['from_date'] = date('Y-m-d', strtotime($data['from_date']));
                $data_arr['to_date'] = date('Y-m-d', strtotime($data['to_date']));
                $data_arr['subject'] = $data['subject'];
                $data_arr['leave_msg'] = $data['leave_msg'];
                $data_arr['student_id'] = $data['uid'];
                $data_arr['admin_id'] = 1;
                $data_arr['status'] = 0; 
                $Datediff = strtotime($data['to_date']) - strtotime($data['from_date']);               
                $data_arr['total_days'] = abs(round($Datediff / 86400)); 
                
                $data_arr = $this->security->xss_clean($data_arr);
                $ins = $this->db_model->insert_data('leave_management',$data_arr);
                if($ins==true){
                    $resp = array('status'=>'true','msg'=>$this->lang->line('ltr_leave_apply_msg'));
                }else{
                    $resp = array('status'=>'false','msg'=>$this->lang->line('ltr_leave_apply_msg'));
                }
            }else{
                $resp = array('status'=>'false','msg'=>$this->lang->line('ltr_missing_parameters_msg')); 
            } 
            echo json_encode($resp,JSON_UNESCAPED_SLASHES);
        
    }
    
    function view_leave(){
        $data = $_REQUEST;
            if(!empty($data['uid'])){
                $student_id = $data['uid'];
                $leave_data = $this->db_model->select_data('id,status, subject, leave_msg as leaveMsg, from_date as fromDate, to_date as toDate, total_days as totalDays, added_at as addedAt','leave_management',array('student_id'=>$student_id),'',array('id','desc'));
		   
                if(!empty($leave_data)){
                    $resp = array('status'=>'true','msg'=>$this->lang->line('ltr_fetch_successfully'), 'leaveData'=>$leave_data);
                }else{
                    $resp = array('status'=>'false','msg'=>$this->lang->line('ltr_no_record_msg'));
                }
            }else{
                $resp = array('status'=>'false','msg'=>$this->lang->line('ltr_missing_parameters_msg')); 
            } 
            echo json_encode($resp,JSON_UNESCAPED_SLASHES);
        
    }
    
    function view_subject_list(){
        $data = $_REQUEST;
            if(!empty($data['batch_id'])){
                $batch_id = $data['batch_id'];
                $arrayData=array();
                if(!empty($batch_id) && empty($data['subject_id']) && empty($data['teacher_id'])){
                    $arrayData= array('batch_id'=>$batch_id);
                    
                }else if(!empty($batch_id) && !empty($data['subject_id']) && empty($data['teacher_id'])){
                    
                    $arrayData= array(
                                    'batch_id'=>$batch_id,
                                    'subject_id'=>$data['subject_id']
                                    );
                    
                }else if(!empty($batch_id) && !empty($data['subject_id']) && !empty($data['teacher_id'])){
                    
                    $arrayData= array(
                                    'batch_id'=>$batch_id,
                                    'subject_id'=>$data['subject_id'],
                                    'teacher_id'=>$data['teacher_id']
                                    );
                    
                }
                
                $sub_data = $this->db_model->select_data('id,teacher_id as teacherId, subject_id as subjectId, chapter','batch_subjects',$arrayData,'',array('id','desc'));
                
                if(!empty($sub_data)){
                    foreach($sub_data as $key=>$value){
                        
                        $sub_name = $this->db_model->select_data('id, subject_name','subjects',array('id'=>$value['subjectId']),'',array('id','desc'));
                        
                        $sub_data[$key]['subjectName']= !empty($sub_name)?$sub_name[0]['subject_name']:'';
                        if(!empty($batch_id) && (!empty($data['subject_id']) || !empty($data['teacher_id']))){
                            $teacher_name = $this->db_model->select_data('id, name','users',array('id'=>$value['teacherId']),'',array('id','desc'));
                            
                            $sub_data[$key]['teacherData']= !empty($teacher_name)?$teacher_name:'';
                            
                            $chap_id =implode(', ', json_decode($value['chapter']));
                            $chapterCon = "id in ($chap_id)";
        	                $chapterName = $this->db_model->select_data('id,chapter_name','chapters',$chapterCon,'');
        	                if(!empty($chapterName)){
        	                    
                                $sub_data[$key]['chapterData']=$chapterName;
        	                }else{
        	                    $sub_data[$key]['chapterData']='';
        	                }
                    
                        }
                        
                        unset($sub_data[$key]['teacherId']);
                        unset($sub_data[$key]['chapter']);
                        
    	                
                    }
                    
                    
                    
                    $resp = array('status'=>'true','msg'=>$this->lang->line('ltr_fetch_successfully'), 'subjectData'=>$sub_data);
                }else{
                    $resp = array('status'=>'false','msg'=>$this->lang->line('ltr_no_record_msg'));
                }
            }else{
                $resp = array('status'=>'false','msg'=>$this->lang->line('ltr_missing_parameters_msg')); 
            } 
            echo json_encode($resp,JSON_UNESCAPED_SLASHES);
        
    }
	
	function doubts_class_ask(){
        $data = $_REQUEST;
		if(!empty($data['batch_id']) && !empty($data['subject_id']) && !empty($data['student_id'])){
			$batch_id = $data['batch_id'];
			$arrayData=array(
							'student_id'=>$data['student_id'],
							'batch_id'=>$data['batch_id'],
							'subjects_id'=>$data['subject_id'],
							);
			if(!empty($data['teacher_id'])){
				$arrayData['teacher_id']= $data['teacher_id'];
			}
			
			if(!empty($data['chapter_id'])){
				$arrayData['chapters_id']= $data['chapter_id'];
			}
			
			if(!empty($data['description'])){
				$arrayData['users_description']= $data['description'];
			}
			
			
			$checkusers = $this->db_model->select_data('doubt_id','student_doubts_class',array('teacher_id'=>$data['teacher_id'],'batch_id'=>$data['batch_id'],'status'=>0,'student_id'=>$data['student_id'],'subjects_id'=>$data['subject_id'],'chapters_id'=>$data['chapter_id']),'',array('doubt_id ','desc'));
			if(empty($checkusers)){
				/*$coundUsers = count($this->db_model->select_data('doubt_id ','student_doubts_class',array('teacher_id'=>$data['teacher_id'],'batch_id'=>$data['batch_id']),'',array('doubt_id ','desc')));
				
				if($coundUsers<=10){*/
					$data_arr = $this->security->xss_clean($arrayData);
					$ins = $this->db_model->insert_data('student_doubts_class',$data_arr);
					
					$doubts_data = array(
									'doubtId'=>$ins,
									'teacherId'=>$data['teacher_id'],
									'studentId'=>$data['student_id'],
									'batchId'=>$data['batch_id'],
									'subjectsId'=>$data['subject_id'],
									'chaptersId'=>$data['chapter_id'],
									'usersDescription'=>$data['description'],
									'teacherDescription'=>'',
									'appointmentDate'=>'',
									'appointmentTime'=>'',
									'createAt'=>date('Y-m-d H:i:s'),
									'status'=>0
									);
					
					$resp = array('status'=>'true','msg'=>$this->lang->line('ltr_doubt_request_msg'), 'doubtsData'=>$doubts_data);
				/*}else{
					$resp = array('status'=>'false','msg'=>$this->lang->line('ltr_something_msg'));
				}*/
				
			}else{
				$resp = array('status'=>'false','msg'=>$this->lang->line('ltr_doubt_request_already_msg'));
			}
		}else{
			$resp = array('status'=>'false','msg'=>$this->lang->line('ltr_missing_parameters_msg')); 
		} 
		echo json_encode($resp,JSON_UNESCAPED_SLASHES);
        
    }
	
	function get_doubts_ask(){
        $data = $_REQUEST;
		if(!empty($data['student_id'])){
			
			$checkusers = $this->db_model->select_data('doubt_id as doubtId, student_id as studentId, teacher_id as teacherId, batch_id as batchId, subjects_id as subjectsId, chapters_id as chaptersId, users_description as usersDescription, teacher_description as teacherDescription, appointment_date as appointmentDate, appointment_time as appointmentTime, create_at as createAt, status','student_doubts_class',array('student_id'=>$data['student_id']),'',array('doubtId ','desc'));
			if(!empty($checkusers)){
				
				foreach($checkusers as $key=>$value){
					
					$teaNam= $this->db_model->select_data('name','users',array('id'=>$value['teacherId']),'',array('id ','desc'));
					$batchNam= $this->db_model->select_data('batch_name','batches',array('id'=>$value['batchId']),'',array('id ','desc'));
					$subNam= $this->db_model->select_data('subject_name','subjects',array('id'=>$value['subjectsId']),'',array('id ','desc'));
					$chrNam= $this->db_model->select_data('chapter_name','chapters',array('id'=>$value['chaptersId']),'',array('id ','desc'));
					
					$checkusers[$key]['teacherName'] = !empty($teaNam[0]['name'])?$teaNam[0]['name']:'';
					$checkusers[$key]['batchName'] = !empty($batchNam[0]['batch_name'])?$batchNam[0]['batch_name']:'';
					$checkusers[$key]['subjectName'] = !empty($subNam[0]['subject_name'])?$subNam[0]['subject_name']:'';
					$checkusers[$key]['chapterName'] = !empty($chrNam[0]['chapter_name'])?$chrNam[0]['chapter_name']:'';
				}
				$resp = array('status'=>'true','msg'=>$this->lang->line('ltr_fetch_successfully'), 'doubtsData'=>$checkusers);
				
			}else{
				$resp = array('status'=>'false','msg'=>$this->lang->line('ltr_no_record_msg'));
			}
		}else{
			$resp = array('status'=>'false','msg'=>$this->lang->line('ltr_missing_parameters_msg')); 
		} 
		echo json_encode($resp,JSON_UNESCAPED_SLASHES);
        
    }
    
	function pay_batch_fee(){
			$data = $_REQUEST;
			if(!empty($data['student_id']) && !empty($data['batch_id'])){
				
				$batch_type =$this->db_model->select_data('batch_type','batches use index (id)',array('id'=>$data['batch_id']),1)[0]['batch_type'];
				
				if($batch_type==2){
					if(!empty($data['transaction_id']) && !empty($data['amount'])){
					
						$chackPayment =$this->db_model->select_data('*','student_payment_history ',array('batch_id'=>$data['batch_id'],'student_id'=>$data['student_id']));
					
						if(empty($chackPayment)){
							$data_pay=array(
							              'student_id'=>$data['student_id'],
										  'batch_id'=>$data['batch_id'],
										  'transaction_id'=>$data['transaction_id'],
										  'amount'=>$data['amount'],
											);
							$data_pay = $this->security->xss_clean($data_pay);
							$ins = $this->db_model->insert_data('student_payment_history',$data_pay);
						}else{
							$arr = array(
								'status'=>'false',
								'msg'=>$this->lang->line('ltr_payment_already')
							);
							echo json_encode($arr);
						    die();
						}
						
					}else{
						$arr = array(
							'status'=>'false',
							'msg'=>$this->lang->line('ltr_missing_parameters_msg'),

						);
						echo json_encode($arr);
						die();
					}
					
				}
				
					//update app version and login status                  
					$this->db_model->update_data_limit('students use index (id)',array('status'=>1,'batch_id'=>$data['batch_id'],'payment_status'=>1),array('id'=>$data['student_id']),1);
					
					$batchData =$this->db_model->select_data('*','batches use index (id)',array('id'=>$data['batch_id']),1);
					
					$studentData =$this->db_model->select_data('id as studentId,email as userEmail,name as fullName,enrollment_id as enrollmentId,contact_no as mobile,app_version as versionCode, batch_id as batchId,admin_id as adminId,admission_date as admissionDate, image,password,token','students use index (id)',array('id'=>$data['student_id']),1);
						$studentData[0]['batchName']=!empty($batchData)?$batchData[0]['batch_name']:'';
						$studentData[0]['image']= base_url('uploads/students/').$studentData[0]['image'];
						$studentData[0]['password'] = $studentData[0]['password'];
						
						$studentData[0]['transactionId'] = !empty($data['transaction_id'])?$data['transaction_id']:'';
        				$studentData[0]['amount'] = !empty($data['amount'])?$data['amount']:'';
						
					$arr = array('status'=>'true', 'msg' =>$this->lang->line('ltr_payment_processed'),'studentData'=>$studentData[0]);
				
				
			}else{
				$arr = array(
					'status'=>'false',
					'msg'=>$this->lang->line('ltr_missing_parameters_msg')
				); 
			}
			echo json_encode($arr);
    }
	
	function get_payment_history(){
        $data = $_REQUEST;
		if(!empty($data['student_id'])){
			
			$payData= $this->db_model->select_data('id, batch_id as batchId,transaction_id as transactionId, amount, create_at as createAt','student_payment_history',array('student_id'=>$data['student_id']),'',array('id ','desc'));
			if(!empty($payData)){
				foreach($payData as $key=>$value){
					$batchData =$this->db_model->select_data('*','batches use index (id)',array('id'=>$value['batchId']),1);
					
					$payData[$key]['batchName'] =!empty($batchData)?$batchData[0]['batch_name']:'';
					$payData[$key]['startDate'] =!empty($batchData)?$batchData[0]['start_date']:'';
					$payData[$key]['endDate'] =!empty($batchData)?$batchData[0]['end_date']:'';
					$payData[$key]['startTime'] =!empty($batchData)?$batchData[0]['start_time']:'';
					$payData[$key]['endTime'] =!empty($batchData)?$batchData[0]['end_time']:'';
					$payData[$key]['batchType'] =!empty($batchData)?$batchData[0]['batch_type']:'';
					$payData[$key]['batchPrice'] =!empty($batchData)?$batchData[0]['batch_price']:'';
					$payData[$key]['description'] =!empty($batchData)?$batchData[0]['description']:'';
					$payData[$key]['status'] =!empty($batchData)?$batchData[0]['status']:'';
					$payData[$key]['batchOfferPrice'] =!empty($batchData)?$batchData[0]['batch_offer_price']:'';
					$payData[$key]['currencyCode'] = $this->general_settings('currency_code');
					$payData[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
				}
				$resp = array('status'=>'true','msg'=>$this->lang->line('ltr_fetch_successfully'), 'paymentData'=>$payData);
				
			}else{
				$resp = array('status'=>'false','msg'=>$this->lang->line('ltr_no_record_msg'));
			}
		}else{
			$resp = array('status'=>'false','msg'=>$this->lang->line('ltr_missing_parameters_msg')); 
		} 
		echo json_encode($resp,JSON_UNESCAPED_SLASHES);
        
    }
    
    function convertCurrency($amount,$from_currency,$to_currency){
          $apikey = $this->general_settings('currency_converter_api');
        
          $from_Currency = urlencode($from_currency);
          $to_Currency = urlencode($to_currency);
          $query =  "{$from_Currency}_{$to_Currency}";
        
          // change to the free URL if you're using the free version
          @$json = file_get_contents("https://free.currconv.com/api/v7/convert?q={$query}&compact=ultra&apiKey={$apikey}");
          $obj = json_decode($json, true);
        
          $val = floatval($obj["$query"]);
        
        
          $total = $val * $amount;
          return number_format($total, 2, '.', '');
        }
    public function SendMail($tomail='', $subject='', $msg=''){
            $frommail =$this->general_settings('smtp_mail');
            $frompwd =$this->general_settings('smtp_pwd');
            $title = $this->db_model->select_data('site_title','site_details','',1,array('id','desc'))[0]['site_title'];

            $this->load->library('email');
            $config = array();
            $config['protocol'] = $this->general_settings('server_type');
            $config['smtp_host'] = $this->general_settings('smtp_host');
            $config['smtp_port'] = $this->general_settings('smtp_port');
            $config['smtp_user'] = $frommail;
            $config['smtp_pass'] = $frompwd;
            $config['charset'] = "utf-8";
            $config['mailtype'] = "html";
            $config['smtp_crypto'] = $this->general_settings('smtp_encryption');
            $config['newline'] = "\r\n";
             $this->email->initialize($config);
            // Set to, from, message, etc.
            
            $this->email->from($frommail, $title);
            $this->email->to($tomail);
            
            $this->email->subject($subject);
            $this->email->message($msg);
            
           @$this->email->send();
            return true;
        }
        function check_batch(){
            $data = $_REQUEST;
    		if(!empty($data['email'])){
    		    $check_email = $this->db_model->select_data('*','students',array('email'=>trim($_POST['email'])));
    		    $check_email_t = $this->db_model->select_data('*','users',array('email'=>trim($_POST['email'])));
    		    if(!empty($check_email_t)){
    		        $resp = array('isEmailExist'=>'true');
    		        echo json_encode($resp,JSON_UNESCAPED_SLASHES);
    		        die();
    		    }
    		    if(!empty($check_email)){
    		        $check_batch = $this->db_model->select_data('*','batches',array('id'=>$check_email[0]['batch_id'],'status'=>1));
    		        if(!empty($check_batch)){
    		            $resp = array('isEmailExist'=>'true');
    		        }else{
    		            $resp = array('isEmailExist'=>'false');
    		        }
    		    }else{
    		        $resp = array('isEmailExist'=>'false');
    		    }
    		}else{
    			$resp = array('status'=>'false','msg'=>$this->lang->line('ltr_missing_parameters_msg')); 
    		} 
    		echo json_encode($resp,JSON_UNESCAPED_SLASHES);
        }
         function reset_password(){
            $data = $_REQUEST;
            if(!empty($data['email'])){
                $check_email = $this->db_model->select_data('*','students',array('email'=>trim($_POST['email'])));
                if(!empty($check_email)){
                    $this->load->library('email');
                    $frommail =$this->general_settings('smtp_mail');
                    $frompwd =$this->general_settings('smtp_pwd');
                    $config = array();
                    $config['protocol'] = $this->general_settings('server_type');
                    $config['smtp_host'] = $this->general_settings('smtp_host');
                    $config['smtp_port'] = $this->general_settings('smtp_port');
                    $config['smtp_user'] = $frommail;
                    $config['smtp_pass'] = $frompwd;
                    $config['charset'] = "utf-8";
                    $config['mailtype'] = "html";
                    $config['smtp_crypto'] = $this->general_settings('smtp_encryption');
                    $config['newline'] = "\r\n";
                    
                    $a=str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                    $pwd = substr($a, 0, 5);
                    
                    $subj = 'Recover your account password '.$this->common->siteTitle;
                    $em_msg = 'Hi '.ucwords($userDetails[0]['name']).',<br/><br/>We have received your request to reset your account password.<br/><br/>Here is your new password : '.$pwd.'<br/><br/> This is an auto-generated email. Please do not reply to this email.';
                    
                    $from_email = $this->common->siteOwnerEmail;
                    $this->email->initialize($config);
                    $this->email->from($from_email, 'Admin'); 
                    $this->email->to($_POST['email']);
                    $this->email->subject($subj); 
                    $this->email->message($em_msg);
                    if($this->email->send()){
                        $data = array( 
                            'password'=>md5($pwd)
                        );
                        $data = $this->security->xss_clean($data);
                        $this->db_model->update_data('students',$data, array('email'=>$_POST['email']));
    
                        $resp = array(
                            'status'=>'true',
                            'msg'=>'We\'ve sent an email to '.$_POST['email'].'.' 
                        );
                    }
                    else{
                        $resp = array(
                            'status'=>'false',
                            'msg'=>$this->lang->line('ltr_something_msg')
                        );
                    }
                }else{
                    $resp = array('status'=>'false','msg'=>$this->lang->line('ltr_email_not_exists_msg'));
                }
            }else{
                $resp = array('status'=>'false','msg'=>$this->lang->line('ltr_missing_parameters_msg')); 
            } 
            echo json_encode($resp,JSON_UNESCAPED_SLASHES);
        }
        
        //new update
        function list_book_notes_paper(){
            $data = $_REQUEST;
    		if(!empty($data['student_id']) && !empty($data['batch_id'])){
    		    
    		    if(!empty($data['student_id'])){
                    $this->viewNotificationStatus($data['student_id'],'book_notes_paper');
                 }
    		    $like = array('batch','"'.$data['batch_id'].'"');
    		    $book_pdf =$this->db_model->select_data('id,admin_id as adminId, title, batch, topic, subject, file_name as fileName,  status, added_by as addedBy, added_at as addedAt','book_pdf use index (id)',array('status'=>1),'',array('id','desc'),$like);
    		    if(!empty($book_pdf)){
    		        foreach($book_pdf as $key=>$value){
    		            $book_pdf[$key]['url']= base_url('uploads/book/');
    		        }
    		    }
    		    $notes_pdf =$this->db_model->select_data('id,admin_id as adminId, title, batch, topic, subject, file_name as fileName,  status, added_by as addedBy, added_at as addedAt','notes_pdf use index (id)',array('status'=>1),'',array('id','desc'),$like);
    		    if(!empty($notes_pdf)){
    		        foreach($notes_pdf as $key=>$value){
    		            $notes_pdf[$key]['url']= base_url('uploads/notes/');
    		        }
    		    }
    		    
                $newexampaper =$this->db_model->select_data('id,admin_id as adminId, title, batch, topic, subject, file_name as fileName,  status, added_by as addedBy, added_at as addedAt','old_paper_pdf use index (id)',array('status'=>1),'',array('id','desc'),$like);
    		    if(!empty($newexampaper)){
    		        foreach($newexampaper as $key=>$value){
    		            $newexampaper[$key]['url']= base_url('uploads/oldpaper/');
    		        }
    		    }
    		    
    		     $resp = array(
                            'status'=>'true',
                            'msg'=>$this->lang->line('ltr_fetch_successfully'),
                            'bookPdf'=> $book_pdf,
                            'notesPdf'=> $notes_pdf,
                            'oldPapers'=>$newexampaper,
                        );
    		    
    		}else{
                $resp = array('status'=>'false','msg'=>$this->lang->line('ltr_missing_parameters_msg')); 
            } 
            echo json_encode($resp,JSON_UNESCAPED_SLASHES);
        }
        
        function changeMyBatch(){
            
            $data = $_REQUEST;
            	$this->db_model->insert_data('temp_data',array('temp'=>json_encode($data)));
    		if(!empty($data['student_id']) && !empty($data['batch_id'])){
    		    
    		    $checkBatch =$this->db_model->select_data('*','sudent_batchs',array('batch_id'=>$data['batch_id'],'student_id'=>$data['student_id']),1);
    		    if(!empty($checkBatch)){
    		        
        		    $studentData =$this->db_model->select_data('id as studentId,email as userEmail,name as fullName,enrollment_id as enrollmentId,contact_no as mobile,app_version as versionCode, batch_id as batchId,admin_id as adminId,admission_date as admissionDate, image, token','students use index (id)',array('id'=>$data['student_id']),1);
    				$studentData[0]['batchName']='';
    				$studentData[0]['image']= base_url('uploads/students/').$studentData[0]['image'];
    				$studentData[0]['password'] = $password;
    				$studentData[0]['paymentType'] = $this->general_settings('payment_type');
    				$studentData[0]['languageName'] = $this->general_settings('language_name');
    				
    				 //check batch type
    				$batch_type =$this->db_model->select_data('*','batches use index (id)',array('id'=>$data['batch_id']),1);
    				
    			    if(!empty($batch_type)){
    			        $studentData[0]['batchName']= $batch_type[0]['batch_name'];
    			        $studentData[0]['batchId']= $batch_type[0]['id'];
    			        if($batch_type[0]['batch_type']==2){
        			            
        			        $batch_his=   $this->db_model->select_data('*','student_payment_history',array('batch_id'=>$data['batch_id'], 'student_id'=>$data['student_id']),1);
        					$studentData[0]['transactionId'] = !empty($batch_his[0]['transaction_id'])?$batch_his[0]['transaction_id']:'';
        			    	$studentData[0]['amount'] = !empty($batch_his[0]['amount'])?$batch_his[0]['amount']:'';
    			            
    			        }else{
    			            $studentData[0]['transactionId'] = 'free';
    			    	    $studentData[0]['amount'] = '';
    			        }
    			    	$update_value =array('batch_id'=>$data['batch_id']);
    				}else{
    				    $resp = array('status'=>'false', 'msg' =>$this->lang->line('ltr_batch_in_msg'));
    				}
    			    
    			    $this->db_model->update_data_limit('students use index (id)',$update_value,array('id'=>$data['student_id']),1);
    			    
    				$resp = array('status'=>'true', 'msg' =>$this->lang->line('ltr_batch_change_msg'),'studentData'=>$studentData[0]);
    				//batch asin
    			   
    		    }else{
    		        
        		    $studentData =$this->db_model->select_data('id as studentId,email as userEmail,name as fullName,enrollment_id as enrollmentId,contact_no as mobile,app_version as versionCode, batch_id as batchId,admin_id as adminId,admission_date as admissionDate, image, token','students use index (id)',array('id'=>$data['student_id']),1);
    				$studentData[0]['batchName']='';
    				$studentData[0]['image']= base_url('uploads/students/').$studentData[0]['image'];
    				$studentData[0]['password'] = $password;
    				$studentData[0]['paymentType'] = $this->general_settings('payment_type');
    				$studentData[0]['languageName'] = $this->general_settings('language_name');
    				
    				 //check batch type
    				$batch_type =$this->db_model->select_data('*','batches use index (id)',array('id'=>$data['batch_id']),1);
    				$studentData[0]['batchId']= $batch_type[0]['id'];
    			    if($batch_type[0]['batch_type']==2){
    			        $studentData[0]['batchName']= $batch_type[0]['batch_name'];
    					$data_pay=array(
    					           'student_id'=>$data['student_id'],
    						       'batch_id'=>$data['batch_id'],
    							   'transaction_id'=> !empty($data['transaction_id'])?$data['transaction_id']:'',
    								  'amount'=> !empty($data['amount'])?$data['amount']:'',
    									);
    					$data_pay = $this->security->xss_clean($data_pay);
    					$insf = $this->db_model->insert_data('student_payment_history',$data_pay);
    					$studentData[0]['transactionId'] = !empty($data['transaction_id'])?$data['transaction_id']:'';
    			    	$studentData[0]['amount'] = !empty($data['amount'])?$data['amount']:'';
    			    	$update_value =array('payment_status'=>1,'batch_id'=>$data['batch_id']);
    				}else{
    				    $studentData[0]['batchName']= $batch_type[0]['batch_name'];
    			    	$studentData[0]['transactionId'] = 'free';
    			    	$studentData[0]['amount'] = !empty($amount)?$amount:'';
    			    	$update_value =array('batch_id'=>$data['batch_id']);
    				}
    			    
    			    $this->db_model->update_data_limit('students use index (id)',$update_value,array('id'=>$data['student_id']),1);
    			    
    				$resp = array('status'=>'true', 'msg' =>$this->lang->line('ltr_account_created'),'studentData'=>$studentData[0]);
    				//batch asin
    			    $data_batch= array(
    			                 'student_id'=>$data['student_id'],
    			                 'batch_id'=>$data['batch_id'],
    			                 'added_by'=>'student'
    					                 );
    		 	   $this->db_model->insert_data('sudent_batchs',$data_batch);
    			    // send email 
    			   $title = $this->db_model->select_data('site_title','site_details','',1,array('id','desc'))[0]['site_title'];
                    $subj = $title.'- '.$this->lang->line('ltr_credentials');
                    $em_msg = $this->lang->line('ltr_hey').' '.ucwords($data['name']).', '.$this->lang->line('ltr_congratulation').' <br/><br/>'.$this->lang->line('ltr_successfully_enrolled').'<br/><br/>'.$this->lang->line('ltr_login_details').'<br/><br/> '.$this->lang->line('ltr_enrolment_id').' : '.$enrolid.'<br/><br/>'.$this->lang->line('ltr_password').' : '.$password.'';
                    $this->SendMail($data['email'], $subj, $em_msg);
        		}
                
            }else{
                $resp = array('status'=>'false','msg'=>$this->lang->line('ltr_missing_parameters_msg')); 
            } 
            echo json_encode($resp,JSON_UNESCAPED_SLASHES);
        }
        
    function db_new_changes_all_batch(){
        $data =	$_REQUEST;
        if(isset($data['admin_id']) && isset($data['student_id'])){
            $admin_id = $data['admin_id'];
            $student_id = $data['student_id'];
            //$batch_id = $data['batch_id'];
            
            $last_login = $this->db_model->select_data('last_login_app','students use index(id)',array('id'=>$student_id),1);
            
            if($last_login[0]['last_login_app'] != '0000-00-00 00:00:00'){
                $batch_details = $this->db_model->select_data('*','sudent_batchs ',array('student_id'=>$student_id),'','','',array('batches','batches.id=sudent_batchs.batch_id'));
                foreach($batch_details as $batch_detail){
                $lastLoginTime = $last_login[0]['last_login_app'];
                $batch_id = $batch_detail['batch_id'];
                $likeEX = array('batch_id','"'.$batch_id.'"');
                $extraData = $this->db_model->select_data('extra_classes.id,extra_classes.admin_id as adminId,extra_classes.date, extra_classes.start_time as startDate,extra_classes.end_time as endTime,extra_classes.teacher_id as teacherId,extra_classes.description,extra_classes.status,extra_classes.batch_id as batchId,extra_classes.added_at as addedAt,extra_classes.completed_date_time as completedDateTime,users.name,users.teach_gender as teachGender','extra_classes use index(id)',array('extra_classes.admin_id'=>$admin_id,'extra_classes.added_at >= '=>$lastLoginTime,'date >=' => date('Y-m-d')),'',array('date','asc'),$likeEX,array('users','users.id = extra_classes.teacher_id'));
                $extraClass =array();
                if(!empty($extraData)){
                    foreach($extraData as $key=>$value){
                        $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'extraClass'));
                        if(empty($view_n)){
                            $extraClass[$key]=$value;
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $extraClass[$key]=$value;
                            }
                        }
                    }
                }
                
                $homewrk = $this->db_model->select_data('homeworks.id, homeworks.admin_id as adminId,homeworks.teacher_id as teacherId, homeworks.date,homeworks.subject_id as subjectId, homeworks.batch_id as batchId, homeworks.description,homeworks.added_at as addedAt,users.name,users.teach_gender as teachGender,subjects.subject_name as subjectName','homeworks use index (id)',array('homeworks.admin_id'=>$admin_id,'homeworks.added_at >= '=>$lastLoginTime,'homeworks.batch_id'=>$batch_id,'date >=' => date('Y-m-d')),'',array('id','desc'),'',array('multiple',array(array('users','users.id = homeworks.teacher_id'),array('subjects','subjects.id = homeworks.subject_id'))),'');
                $homewrkData=array();
                if(!empty($homewrk)){
                    foreach($homewrk as $key=>$value){
                     $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'homeWork'));
                        if(empty($view_n)){
                            $homewrkData[$key]=$value;
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $homewrkData[$key]=$value;
                            }
                        }
                    }
                }
                
                $likev = array('batch','"'.$batch_id.'"');
                $videosData = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt, video_type as videoType','video_lectures use index (id)',array('admin_id'=>$admin_id,'added_at >= '=>$lastLoginTime),'',array('id','desc'),$likev);
                $videos =array();
               
                if(!empty($videosData)){
                    foreach($videosData as $key=>$value){
                        $sub_id =$this->db_model->select_data('subject_id','batch_subjects',array('batch_id'=>$batch_id),1);
                        if(!empty($sub_id)){
                            $sub_name =$this->db_model->select_data('subject_name','subjects',array('id'=>$sub_id[0]['subject_id']),1);
                            if(!empty($sub_name)){
                               // if($sub_name[0]['subject_name']==$value['subject']){
                                    $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id'=>$student_id,'notice_type'=>'videoLecture'));
                                    if(empty($view_n)){
                                        $videos[$key]=$value;
                                        $url = $value['url'];
                                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                        $videos[$key]['videoId']=$match[1];
                                    }else{
                                        if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                            $videos[$key]=$value;
                                            $url = $value['url'];
                                        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                        $videos[$key]['videoId']=$match[1];
                                        }
                                    }
                            //  }
                            }
                        }
                        
                    }
                }
                
                $vacancyData = $this->db_model->select_data('id,title,description,start_date as startDate,last_date as lastDate,mode,files,status,admin_id as adminId,added_at as addedAt','vacancy use index (id)',array('admin_id'=>$admin_id,'last_date >= '=> date("Y-m-d"),'status'=>1,'added_at >='=>$lastLoginTime),'',array('id','desc'));
                $vacancy=array();
                if(!empty($vacancyData)){
                    foreach($vacancyData as $key=>$value){
                        $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'vacancy'));
                        if(empty($view_n)){
                            $vacancy[$key]=$value;
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $vacancy[$key]=$value;
                            }
                        }
                    }
                }
                
                $noticesData = $this->db_model->select_data('`id`, `title`, `description`, `notice_for` as noticeFor, `status`, `date`, `admin_id` as adminId, `student_id` as studentId, `teacher_id` as teacherId, `added_at` as addedAt, `added_by` as addedBy, `read_status` as readStatus','notices use index (id)',"admin_id = $admin_id AND status = 1 AND (notice_for = 'Student' OR notice_for = 'Both' OR student_id = $student_id) AND added_at >= '$lastLoginTime' AND date >= CURDATE()",'',array('id','desc'));
                
                $notices=array();
                if(!empty($noticesData)){
                    foreach($noticesData as $key=>$value){
                        $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id'=>$student_id,'notice_type'=>'notices'));
                        if(empty($view_n)){
                            $notices[$key]=$value;
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $notices[$key]=$value;
                            }
                        }
                    }
                }
                
                $practice_paper = $this->db_model->select_data('id,name,time_duration as timeDuration, added_at as addedAt, total_question as totalQuestion','exams use index (id)',array('type'=>2,'admin_id'=>$admin_id,'status'=>1,'batch_id'=>$batch_id,'added_at >='=>$lastLoginTime),'',array('id','desc'));
                
                 $newexampaper_test = array();       
                if(!empty($practice_paper)){
                    foreach($practice_paper as $key=>$pexam){
                            $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id'=>$student_id,'notice_type'=>'practicePaper'));
                            $paperstatus = $this->db_model->select_data('id','practice_result use index (id)',array('admin_id' => $admin_id,'student_id' => $student_id,'paper_id' => $pexam['id']),1);
                         
                            if(empty($paperstatus) && empty($view_n)){
                               // array_push($newexampaper_test,$pexam); 
                               $newexampaper_test[$key]=$pexam;
                            }else{
                                if(!empty($view_n)){
                        
                                    if(strtotime($view_n[0]['views_time'])<strtotime($pexam['addedAt'])){
                                        $newexampaper_test[$key]=$pexam;
                                        
                                      }
                                  
                                }
                            }
                        }
                    }
                    
                    
                $mock_paper = $this->db_model->select_data('id,name,time_duration as timeDuration, added_at as addedAt, total_question as  totalQuestion,mock_sheduled_date as mockSheduledDate,mock_sheduled_time as mockSheduledTime','exams use index (id)',array('type' => 1,'admin_id' => $admin_id,'status' => 1,'batch_id' => $batch_id,'mock_sheduled_date >=' => date('Y-m-d'),'added_at >='=>$lastLoginTime),'',array('id','desc'));
               
                $newexampaper = array();          
                if(!empty($mock_paper)){
                    foreach($mock_paper as $exam){
                            $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'mockPaper'));
                            $paperstatus = $this->db_model->select_data('id','mock_result use index (id)',array('admin_id' => $admin_id,'student_id' => $student_id,'paper_id' => $exam['id']),1);
                            if($exam['mockSheduledDate']==date('Y-m-d')){
                                if(strtotime($exam['mockSheduledTime'])>strtotime(date('H:i:s'))){
                                    if(empty($paperstatus) && empty($view_n)){
                                array_push($newexampaper,$exam); 
                                    }else{
                                        if(!empty($view_n)){
                                            if(strtotime($view_n[0]['views_time'])<strtotime($exam['addedAt'])){
                                                array_push($newexampaper,$exam);
                                              }
                                          
                                        }
                                    }
                                }
                            }else{
                                if(empty($paperstatus) && empty($view_n)){
                                array_push($newexampaper,$exam); 
                                    }else{
                                        if(!empty($view_n)){
                                            if(strtotime($view_n[0]['views_time'])<strtotime($exam['addedAt'])){
                                                array_push($newexampaper,$exam);
                                              }
                                          
                                        }
                                    }
                            }
                         
                            
                        }
                    }
                    
                $addNewBook=array();
                $like = array('batch','"'.$batch_id.'"');
    		    $book_pdf =$this->db_model->select_data('id,admin_id as adminId, title, batch, topic, subject, file_name as fileName,  status, added_by as addedBy, added_at as addedAt','book_pdf use index (id)',array('status'=>1,'added_at >= '=>$lastLoginTime),'',array('id','desc'),$like);
    		    if(!empty($book_pdf)){
    		        foreach($book_pdf as $key=>$value){
    		            $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'book_notes_paper'));
                        if(empty($view_n)){
                            $addNewBook[$key]=$value;
                            $addNewBook[$key]['url']= base_url('uploads/book/');
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $addNewBook[$key]=$value;
                                $addNewBook[$key]['url']= base_url('uploads/book/');
                            }
                        }
    		        }
    		    }
    		    
    		    $addNewNotes=array();
    		    $notes_pdf =$this->db_model->select_data('id,admin_id as adminId, title, batch, topic, subject, file_name as fileName,  status, added_by as addedBy, added_at as addedAt','notes_pdf use index (id)',array('status'=>1,'added_at >= '=>$lastLoginTime),'',array('id','desc'),$like);
    		    if(!empty($notes_pdf)){
    		        foreach($notes_pdf as $key=>$value){
    		            
    		            $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'book_notes_paper'));
                        if(empty($view_n)){
                            $addNewNotes[$key]=$value;
                            $addNewNotes[$key]['url']= base_url('uploads/notes/');
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $addNewNotes[$key]=$value;
                                $addNewNotes[$key]['url']= base_url('uploads/notes/');
                            }
                        }
    		        }
    		    }
    		    
    		    $addOldPaper=array();
                $newexampaper =$this->db_model->select_data('id,admin_id as adminId, title, batch, topic, subject, file_name as fileName,  status, added_by as addedBy, added_at as addedAt','old_paper_pdf use index (id)',array('status'=>1,'added_at >= '=>$lastLoginTime),'',array('id','desc'),$like);
    		    if(!empty($newexampaper)){
    		        foreach($newexampaper as $key=>$value){
    		            
    		            $view_n =$this->db_model->select_data('views_time','views_notification_student',array('student_id >= '=>$student_id,'notice_type'=>'book_notes_paper'));
                        if(empty($view_n)){
                            $addOldPaper[$key]=$value;
                            $addOldPaper[$key]['url']= base_url('uploads/oldpaper/');
                        }else{
                            if(strtotime($view_n[0]['views_time'])<strtotime($value['addedAt'])){
                                $addOldPaper[$key]=$value;
                                $addOldPaper[$key]['url']= base_url('uploads/oldpaper/');
                            }
                        }
    		        }
    		    }
    		    $classdata=array();
    		    $classdata = current($this->db_model->select_data('users.name,users.teach_image AS teachImage,subjects.subject_name as subjectName,chapters.chapter_name as chapterName,live_class_history.end_time as endTime','live_class_history',array('batch_id'=>$batch_id),'1',array('live_class_history.id','desc'),'',array('multiple',array(array('users','users.id = live_class_history.uid'),array('subjects','subjects.id = live_class_history.subject_id'),array('chapters','chapters.id = live_class_history.chapter_id')))));
                $exam = $this->db_model->select_data('id,name','exams  use index (id)',array('batch_id'=>$batch_id,'type'=>1,'mock_sheduled_date <='=>date('Y-m-d')),'1',array('id','desc'));
            $top_three = $this->db_model->select_data('mock_result.paper_name as paperName,students.name,students.image,mock_result.percentage','mock_result  use index (id)',array('paper_id'=>$exam[0]['id'],'mock_result.percentage >'=>0),'3',array('mock_result.percentage','desc'),'',array('students','students.id=mock_result.student_id'));
                $arr_dd = array(
                    'topThreeData' => $top_three,
                    'filesUrl' => base_url('uploads/students/')
                );
    		         
    		         
                $arr1[] = array(
                    'batchName'=>$batch_detail['batch_name'],
                    'batchId'=>$batch_detail['id'],
                    'extraClass'=> $extraClass,
                    'homeWork'=>$homewrkData,
                    'videoLecture'=>$videos,
                    'vacancy'=>$vacancy,
                    'notices'=>$notices,
                    'practicePaper'=>$newexampaper_test,
                    'mockPaper'=>$newexampaper,
                    'addOldPaper'=>$addOldPaper,
                    'addNewBook'=>$addNewBook,
                    'addNewNotes'=>$addNewNotes,
                    'liveClass' => ($classdata['endTime']=='' && !empty($classdata))?$classdata:null,
                    'topThree' => $arr_dd,
                ); 
                }
                $arr['status']='true';
                $arr['msg']= $this->lang->line('ltr_fetch_successfully');
                $arr['noticesCount']=count($notices) + count($extraClass) + count($homewrkData) + count($videos) + count($vacancy) + count($addNewNotes) + count($addNewBook) + count($addOldPaper) + count($newexampaper) + count($newexampaper_test);
                $arr['data']=$arr1;
                
            }else{
                $arr = array('status'=>'false','msg'=>$this->lang->line('ltr_its_first_login'));
            }
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
    
    function get_syllabus_data(){
        
          $data =	$_REQUEST;
        if(isset($data['batch_id'])){
            $join = array('batch_subjects',"batch_subjects.batch_id = batches.id");
            $batchData = $this->db_model->select_data('batch_subjects.*, batches.batch_name as batchName','batches use index (id)',array('status'=>1,'batch_id'=>$data['batch_id']),'',array('id','desc'),'',$join);  
            
          if(!empty($batchData)){
            foreach($batchData as $key => $value){
                            
            $data_arr['batchId'] = $batchData[$key]['batch_id'];
            $data_arr['batchName'] = $batchData[$key]['batchName'];
            $data_arr['teacherId'] = $batchData[$key]['teacher_id'];
            // $data_arr['chapterStatus']=implode(', ',json_decode($batchData[$key]['chapter_status']));
             $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$data_arr['batchId']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
				
					   if(!empty($batchSubject)){
					       foreach($batchSubject as $skey=>$svalue){
					            $cid=implode(', ',json_decode($svalue['chapter']));
					            $complete_id = json_decode($value['chapter_status']);
					           
					            $con ="id in ($cid)";
					            $chapter = $this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
					            if(!empty($chapter)){
					                foreach($chapter as $ckey=>$cvalue){
					              if (in_array($cvalue['id'], $complete_id))
                                {
                                 $chapter[$ckey]['complete'] = true;
                                  }
                                else
                                  {
                                    $chapter[$ckey]['complete'] = false;
                                  }
					              
					                }
					                $batchSubject[$skey]['chapter']= $chapter;
					              
					            }else{
					             $batchSubject[$skey]['chapter']=array();   
					            }
					            
					       }
					       
					       $data_arr['batchSubject']= $batchSubject;
					   }else{
					     $data_arr['batchSubject'] = array();  
					   }
            }
                      
                   
              $arr = array(
                 'data' => $data_arr,
                'status'=>'True',
                'msg'=>$this->lang->line('ltr_fetch_successfully')
            ); 
        }else{
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_no_record_msg')
            ); 
        }
 }
        else{  
            $arr = array(
                'status'=>'false',
                'msg'=>$this->lang->line('ltr_missing_parameters_msg')
            ); 
        }
        echo json_encode($arr);
    }
  
  
    function get_all_data(){
        $data = $_REQUEST;
        
        $subcat = $data['subcat'];
        if(!empty($subcat)){
       
                 
      if(isset($data['length']) && $data['length']>0){
            if(isset($data['start']) && !empty($data['start'])){
                $limit = array($data['length'],$data['start']);
                // $count = $data['start']+1;
            }else{ 
                $limit = array($data['length'],0);
                // $count = 1;
            }
        }else{
            $limit = '';
            // $count = 1;
        }
        
         $slider_limit = $data['limit'];
        $category = $this->db_model->select_data('id as categoryId,name as categoryName','batch_category use index (id)',array('status'=>1));
                     if(!empty($category)){
                        
                        foreach($category as $catkey=>$value){
                                 

            $cond = array('status'=>1,'cat_id'=>$value['categoryId'],'id'=>$subcat);
            
                    $subCategory = $this->db_model->select_data('id as SubcategoryId,name as SubcategoryName','batch_subcategory use index (id)',$cond,'','');
            
                    if(!empty($subCategory)){
                        
                        foreach($subCategory as $subkey=>$value){
                    
                     $search = trim($data['search']);
                    if(!empty($search)){
                      $like_search = array('batch_name',$search);
                       $cond_sub1='';
                    }
                    
                    else{
                        
                        $like_search = '';
                        $cond_sub1 = array('status'=>1,'sub_cat_id'=>$value['SubcategoryId']);
                    }
                  
                   
$batchData = $this->db_model->select_data('id, batch_name as batchName, start_date as startDate, end_date as endDate, start_time as startTime, end_time as endTime, batch_type as batchType, batch_price as batchPrice, no_of_student as noOfStudent ,status,description, batch_image as batchImage, batch_offer_price as batchOfferPrice','batches use index (id)',$cond_sub1,$limit,array('id','desc'),$like_search);
 
            if(!empty($batchData)){
                foreach($batchData as $key=>$value){
                    if(!empty($value['batchImage'])){
                        $batchData[$key]['batchImage']= base_url('uploads/batch_image/').$value['batchImage'];
                    }
                    $startDate =$value['startDate'];
                    $endDate =$value['endDate'];
                    $batchData[$key]['startDate']=date('d-m-Y',strtotime($startDate));
                    $batchData[$key]['endDate']=date('d-m-Y',strtotime($endDate));
                   
                    $batch_fecherd =$this->db_model->select_data('batch_specification_heading as batchSpecification, batch_fecherd as fecherd','batch_fecherd',array('batch_id'=>$value['id']));
                    if(!empty($batch_fecherd)){
                           $batchData[$key]['batchFecherd'] =$batch_fecherd;
                    }else{
                        $batchData[$key]['batchFecherd']=array();
                    }

                   // add payment type
                   $batchData[$key]['paymentType'] = $this->general_settings('payment_type');
                   $batchData[$key]['currencyCode'] = $this->general_settings('currency_code');
                   $batchData[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
                   
                   
                   $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$value['id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
                   if(!empty($batchSubject)){
                       foreach($batchSubject as $skey=>$svalue){
                            $cid=implode(', ',json_decode($svalue['chapter']));
                            $con ="id in ($cid)";
                            $chapter =$this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
                            if(!empty($chapter)){
                                foreach($chapter as $ckey=>$cvalue){
                                    
                                    $sub_like = array('topic,batch',urldecode($cvalue['chapterName']).',"'.$value['id'].'"');
                                    $sub_videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$sub_like);
                                    if(!empty($sub_videos)){
                                        foreach($sub_videos as $vkey=>$vvalue){
                                            $url = $vvalue['url'];
                                            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                            $sub_videos[$vkey]['videoId']=$match[1];
                                        }
                                        $chapter[$ckey]['videoLectures']=$sub_videos;
                                    }else{
                                      $chapter[$ckey]['videoLectures'] = array(); 
                                    }
                                    
                                    
                                }
                                $batchSubject[$skey]['chapter']=$chapter;
                            }else{
                             $batchSubject[$skey]['chapter']=array();   
                            }
                       
                       }
                       
                       $batchData[$key]['batchSubject'] = $batchSubject;
                   }else{
                     $batchData[$key]['batchSubject'] = array();  
                   }
                    $like = array('batch','"'.$value['id'].'"');
                    $videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$like);
                    if(!empty($videos)){
                        foreach($videos as $vkey=>$vvalue){
                            $url = $vvalue['url'];
                            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                            $videos[$vkey]['videoId']=$match[1];
                        }
                        $batchData[$key]['videoLectures']=$videos;
                    }else{
                      $batchData[$key]['videoLectures'] = array(); 
                    }
                   $student_batch_dtail = $this->db_model->select_data('*','sudent_batchs',array('student_id'=>$data['student_id'],'batch_id' => $value['id'] ),'');
                   if(!empty($student_batch_dtail)){
                        $batchData[$key]['purchase_condition'] = true;
                   }else{
                       $batchData[$key]['purchase_condition'] = false;
                   }
                }
                    $subCategory[$subkey]['BatchData'] = $batchData;
                    }else{
                       
                                $subCategory[$subkey]['BatchData'] = array();
                               }
                        }
                         $category[$catkey]['subcategory'] = $subCategory;
                   }else{
                           $category[$catkey]['subcategory'] = array();
                   }
                  
                }
            
            }
                    
                 if($data['subcat']=='other'){
                    $getOther_Batch=$this->otherBatch_data($data);
                 /*print_r($getOther_Batch);
                die();*/
                    if($getOther_Batch){
                        array_push($category,$getOther_Batch);
                    }
                 }
                        
                     $category=array_values($category);    
            $arr = array(           
                            'status'=>'true',
                            'msg' =>$this->lang->line('ltr_fetch_successfully'),
                            'batchData'=>$category
                            );
        }
            
   //     elseif(!empty($data['search'])){
            
   //       $search = trim($data['search']);
         // $like_search = array('batch_name',$search);
          
   // 	if(isset($data['length']) && $data['length']>0){
//             if(isset($data['start']) && !empty($data['start'])){
//                 $limit = array($data['length'],$data['start']);
//                 // $count = $data['start']+1;
//             }else{ 
//                 $limit = array($data['length'],0);
//                 // $count = 1;
//             }
//         }else{
//             $limit = '';
//             // $count = 1;
//         }

   //         //  $slider_limit = $data['limit'];
   //         $category = $this->db_model->select_data('id as categoryId,name as categoryName','batch_category use index (id)',array('status'=>1),$limit);
            // 	     if(!empty($category)){
                        
            // 	        foreach($category as $catkey=>$value){
                                 
            // 	   //  }
                   
            // 	   // $cond="id not in ($batch_id) AND status=1 AND cat_id= $value['id']";
            // 	   $cond = array('status'=>1,'cat_id'=>$value['categoryId']);
                   
            // 	     $subCategory = $this->db_model->select_data('id as SubcategoryId,name as SubcategoryName','batch_subcategory use index (id)',$cond,'','',array('id',$data['subcat']));
            // 	   // echo $this->db->last_query();
            // 	    if(!empty($subCategory)){
                        
            // 	        foreach($subCategory as $subkey=>$value){
                    
                     
            // 	   $cond_sub1 = array('status'=>1,'sub_cat_id'=>$value['SubcategoryId']);
                   
   //                $batchData = $this->db_model->select_data('id, batch_name as batchName, start_date as startDate, end_date as endDate, start_time as startTime, end_time as endTime, batch_type as batchType, batch_price as batchPrice, no_of_student as noOfStudent ,status,description, batch_image as batchImage, batch_offer_price as batchOfferPrice','batches use index (id)',$cond_sub1,$slider_limit,array('id','desc'),$like_search);
   
            // if(!empty($batchData)){
            // 	foreach($batchData as $key=>$value){
            // 		if(!empty($value['batchImage'])){
            // 			$batchData[$key]['batchImage']= base_url('uploads/batch_image/').$value['batchImage'];
            // 		}
            // 		$startDate =$value['startDate'];
            // 		$endDate =$value['endDate'];
//                     $batchData[$key]['startDate']=date('d-m-Y',strtotime($startDate));
//                     $batchData[$key]['endDate']=date('d-m-Y',strtotime($endDate));
                   
            // 		$batch_fecherd =$this->db_model->select_data('batch_specification_heading as batchSpecification, batch_fecherd as fecherd','batch_fecherd',array('batch_id'=>$value['id']));
            // 		if(!empty($batch_fecherd)){
            // 			   $batchData[$key]['batchFecherd'] =$batch_fecherd;
            // 		}else{
            // 			$batchData[$key]['batchFecherd']=array();
            // 		}

            // 	   // add payment type
            // 	   $batchData[$key]['paymentType'] = $this->general_settings('payment_type');
            // 	   $batchData[$key]['currencyCode'] = $this->general_settings('currency_code');
            // 	   $batchData[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
                   
                   
            // 	   $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$value['id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
            // 	   if(!empty($batchSubject)){
            // 	       foreach($batchSubject as $skey=>$svalue){
            // 	            $cid=implode(', ',json_decode($svalue['chapter']));
            // 	            $con ="id in ($cid)";
            // 	            $chapter =$this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
            // 	            if(!empty($chapter)){
            // 	                foreach($chapter as $ckey=>$cvalue){
                                    
            // 	                    $sub_like = array('topic,batch',urldecode($cvalue['chapterName']).',"'.$value['id'].'"');
//             					    $sub_videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$sub_like);
//                                     if(!empty($sub_videos)){
//                                         foreach($sub_videos as $vkey=>$vvalue){
//                                             $url = $vvalue['url'];
//                                             preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
//                                             $sub_videos[$vkey]['videoId']=$match[1];
//                                         }
//                                         $chapter[$ckey]['videoLectures']=$sub_videos;
//                                     }else{
//                                       $chapter[$ckey]['videoLectures'] = array(); 
//                                     }
                                    
                                    
            // 	                }
            // 	                $batchSubject[$skey]['chapter']=$chapter;
            // 	            }else{
            // 	             $batchSubject[$skey]['chapter']=array();   
            // 	            }
                            
                            
            // 	       }
                       
            // 	       $batchData[$key]['batchSubject'] = $batchSubject;
            // 	   }else{
            // 	     $batchData[$key]['batchSubject'] = array();  
            // 	   }
            // 	    $like = array('batch','"'.$value['id'].'"');
            // 	    $videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$like);
//                     if(!empty($videos)){
//                         foreach($videos as $vkey=>$vvalue){
//                             $url = $vvalue['url'];
//                             preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
//                             $videos[$vkey]['videoId']=$match[1];
//                         }
//                         $batchData[$key]['videoLectures']=$videos;
//                     }else{
//                       $batchData[$key]['videoLectures'] = array(); 
//                     }
            // 	   $student_batch_dtail = $this->db_model->select_data('*','sudent_batchs',array('student_id'=>$data['student_id'],'batch_id' => $value['id'] ),'');
            // 	   if(!empty($student_batch_dtail)){
            // 	        $batchData[$key]['purchase_condition'] = true;
            // 	   }else{
            // 	       $batchData[$key]['purchase_condition'] = false;
            // 	   }
            // 	}
//     			$subCategory[$subkey]['BatchData'] = $batchData;
            
            // 	    }else{
                       
//     					   	 $subCategory[$subkey]['BatchData'] = array();
//     					   	}
            // 			}
            // 			 $category[$catkey]['subcategory'] = $subCategory;
            // 	   }else{
            // 	   		$category[$catkey]['subcategory'] = array();
            // 	   }
                  
            // 	}
            // }

   //         $arr = array(           
   //                         'status'=>'true',
            //                 'msg' =>$this->lang->line('ltr_fetch_successfully'),
            //                 'batchData'=>$category
            //                 );
              
            // }
            
            else{
                $arr = array('status'=>'false', 'msg' => $this->lang->line('ltr_missing_parameters_msg')); 
            }
        echo json_encode($arr);
    }

    public function otherBatch_data($data){
        $search = trim($data['search']);
        $slider_limit = $data['limit'];
        
        if(!empty($search)){
          $like_search = array('batch_name',$search);
        }else{
            $like_search = '';
        }

      if(isset($data['length']) && $data['length']>0){
            if(isset($data['start']) && !empty($data['start'])){
                $limit = array($data['length'],$data['start']);
                // $count = $data['start']+1;
            }else{ 
                $limit = array($data['length'],0);
                // $count = 1;
            }
        }else{
            $limit = '';
            // $count = 1;
        }
                     $category=array('categoryId'=>'0','categoryName'=>'other');
                     $subCategory['SubcategoryId']='0';
                     $subCategory['SubcategoryName']='other';
                   $cond_sub1 = array('status'=>1,'sub_cat_id'=>0);
                   
            $batchData = $this->db_model->select_data('id, batch_name as batchName, start_date as startDate, end_date as endDate, start_time as startTime, end_time as endTime, batch_type as batchType, batch_price as batchPrice, no_of_student as noOfStudent ,status,description, batch_image as batchImage, batch_offer_price as batchOfferPrice','batches use index (id)',$cond_sub1,$limit,array('id','desc'),$like_search);

            if(!empty($batchData)){
                foreach($batchData as $key=>$value){
                    if(!empty($value['batchImage'])){
                        $batchData[$key]['batchImage']= base_url('uploads/batch_image/').$value['batchImage'];
                    }
                    $startDate =$value['startDate'];
                    $endDate =$value['endDate'];
                    $batchData[$key]['startDate']=date('d-m-Y',strtotime($startDate));
                    $batchData[$key]['endDate']=date('d-m-Y',strtotime($endDate));
                   
                    $batch_fecherd =$this->db_model->select_data('batch_specification_heading as batchSpecification, batch_fecherd as fecherd','batch_fecherd',array('batch_id'=>$value['id']));
                    if(!empty($batch_fecherd)){
                           $batchData[$key]['batchFecherd'] =$batch_fecherd;
                    }else{
                        $batchData[$key]['batchFecherd']=array();
                    }

                   // add payment type
                   $batchData[$key]['paymentType'] = $this->general_settings('payment_type');
                   $batchData[$key]['currencyCode'] = $this->general_settings('currency_code');
                   $batchData[$key]['currencyDecimalCode'] = $this->general_settings('currency_decimal_code');
                   
                   
                   $batchSubject = $this->db_model->select_data('subjects.id,subjects.subject_name as subjectName, batch_subjects.chapter','subjects use index (id)',array('batch_id'=>$value['id']),'',array('id','desc'),'',array('batch_subjects','batch_subjects.subject_id=subjects.id'));
                   if(!empty($batchSubject)){
                       foreach($batchSubject as $skey=>$svalue){
                            $cid=implode(', ',json_decode($svalue['chapter']));
                              if(!empty($cid)){
                            $con ="id in ($cid)";
                            $chapter =$this->db_model->select_data('id,chapter_name as chapterName','chapters use index (id)',$con,'',array('id','desc'));
                            if(!empty($chapter)){
                                foreach($chapter as $ckey=>$cvalue){
                                    
                                    $sub_like = array('topic,batch',urldecode($cvalue['chapterName']).',"'.$value['id'].'"');
                                    $sub_videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$sub_like);
                                    if(!empty($sub_videos)){
                                        foreach($sub_videos as $vkey=>$vvalue){
                                            $url = $vvalue['url'];
                                            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                                            $sub_videos[$vkey]['videoId']=$match[1];
                                        }
                                        $chapter[$ckey]['videoLectures']=$sub_videos;
                                    }else{
                                      $chapter[$ckey]['videoLectures'] = array(); 
                                    }
                                    
                                }
                                $batchSubject[$skey]['chapter']=$chapter;
                            }else{
                             $batchSubject[$skey]['chapter']=array();   
                            }
                              }else{
                             $batchSubject[$skey]['chapter']=array();   
                            }
                       }
                       
                       $batchData[$key]['batchSubject'] = $batchSubject;
                   }else{
                     $batchData[$key]['batchSubject'] = array();  
                   }
                    $like = array('batch','"'.$value['id'].'"');
                    $videos = $this->db_model->select_data('id,admin_id as adminId,title,batch,topic,subject,url,status,added_by as addedBy,added_at as addedAt,video_type as videoType, preview_type as previewType, description','video_lectures use index (id)',array('status'=>1),'',array('id','desc'),$like);
                    if(!empty($videos)){
                        foreach($videos as $vkey=>$vvalue){
                            $url = $vvalue['url'];
                            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
                            $videos[$vkey]['videoId']=$match[1];
                        }
                        $batchData[$key]['videoLectures']=$videos;
                    }else{
                      $batchData[$key]['videoLectures'] = array(); 
                    }
                   $student_batch_dtail = $this->db_model->select_data('*','sudent_batchs',array('student_id'=>$data['student_id'],'batch_id' => $value['id'] ),'');
                   
                   
                   if(!empty($student_batch_dtail)){
                        $batchData[$key]['purchase_condition'] = true;
                   }else{
                       $batchData[$key]['purchase_condition'] = false;
                   }
        
                    $subCategory['BatchData'] = $batchData;
                
                }
                    }else{
                                $subCategory['BatchData'] = array();
                               }
                    
                         $category['subcategory'][] = $subCategory;
                    
                         if(!empty($category)){
                               return $category;
                           }else{
                               return false;
                           }
    }
    
   	public function certificate(){
      
        $data = $_REQUEST;
         if(isset($data['student_id'])){
             $id=$data['student_id'];
            //  $batch_id = $data['batch_id'];
            $cond = array('sudent_batchs.student_id' => $id);
            $table = 'batches';
            $join = array('batches', 'sudent_batchs.batch_id = batches.id');
            $batches = $this->db_model->select_data('batches.batch_name,sudent_batchs.batch_id,sudent_batchs.student_id','sudent_batchs',$cond,$limit,array('sudent_batchs.id','desc'),$like,$join,'',$or_like);
            
            if(!empty($batches)){
                foreach($batches as $key => $cert){
                    $data['batchdata'] = $cert;
                    $data['certify'] = $this->db_model->select_data('*','certificate',array('student_id'=>$cert['student_id'],'batch_id'=>$cert['batch_id']));
                    $data['certificate_details']=$this->db_model->select_data('*','certificate_setting','',1,array('id','desc'));
                    $data['site_details_logo']=$this->db_model->select_data('site_logo','site_details','',1,array('id','desc'));
                    $data['student_details']=$this->db_model->select_data('name','students',array('id'=>$id),1,array('id','desc'));
                    // $data['batchdata']=$this->db_model->select_data('batch_name','batches',array('id'=>$batch_id),1,array('id','desc'));
                    $data['baseurl'] = base_url();
                    //print_r($data);
                    $html=	$this->load->view("student/certificate_pdf",$data,true); 
                    $this->load->library('pdf'); // change to pdf_ssl for ssl
                    // $filename = "certificate_".$id."_".$cert['batch_id'];
                    $batches[$key]['filename']= "certificate_".$id."_".$cert['batch_id'].'.pdf';
                    $batches[$key]['batch_name']= $cert['batch_name'];
                    $batches[$key]['filesUrl']= base_url('uploads/certificate/');
                    $batches[$key]['assign'] = $data['certify'] ? true : false;
                    $result=$this->pdf->create($html);
                    $file_path= explode("application",APPPATH);
                    file_put_contents($file_path[0].'uploads/certificate/'.$batches[$key]['filename'], $result);
                   
                }

                $arr = array(
                    'data' => $batches,
                    'status' => 'true',
                    'msg' => $this->lang->line('ltr_fetch_successfully')
                );
           
             }
             else{
                 $arr = array(
                     'status' => 'false',
                     'msg' => $this->lang->line('ltr_no_record_msg')
                 );
             }
         }else{
             $arr = array(
                 'status'=>'false',
                 'msg'=>$this->lang->line('ltr_missing_parameters_msg')
             );
         }
         echo json_encode($arr);
    }
    
}