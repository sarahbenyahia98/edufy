<!----- Courses Single Section ----->
<section class="edu_courses_single">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-12 padder0">
				<div class="edu_courses_section">
					<div class="row">
    					<div class="col-xl-7 col-lg-5 col-md-12 col-sm-12 col-12 align-self-center">
    						<div class="edu_courses_imgbox">
    						    <img src="<?php if(!empty($singel_batches[0]['batch_image'])) { echo base_url('uploads\batch_image/').$singel_batches[0]['batch_image'] ; }else{ echo base_url('uploads/site_data/'.$site_Details['0']['site_logo']); } ?>" alt="image">
    						</div>
    					</div>
    				    <div class="col-xl-5 col-lg-7 col-md-12 col-sm-12 col-12 align-self-center">
    				        <div class="edu_courses_box">
    						    <div class="edu_courses_cntnbox">
    						    <h2 class="edu_courses_sprice"><?php if($singel_batches[0]['batch_type']==2){ if(!empty($singel_batches[0]['batch_offer_price'])){ echo '<s>'.$currency_decimal.' '.$singel_batches[0]['batch_price'].'</s> / '.$currency_decimal.' '.$singel_batches[0]['batch_offer_price']; }else{ echo $currency_decimal.' '.$singel_batches[0]['batch_price'];} }else{ echo "Free";} ?></h2>
    						    
    							<h2 class="edu_courses_title"><?php echo $singel_batches[0]['batch_name']; ?></h2>
    							<p class="edu_courses_des mb_30"><?php echo $singel_batches[0]['description']; ?></p>
								
								<a href="<?php echo base_url('enroll-now/'.$singel_batches[0]['id']); ?>" class="edu_btn"><?php echo html_escape($this->common->languageTranslator('ltr_enroll_now'));?></a>
    						    </div>
    						</div>
    				    </div>
    				</div>
				</div>
			</div>
		</div>
	</div>
</section>


	
	<!-- course learn start -->

<div class="edu-cousre-learn spacer-top spacer-bottom edu-course-bg-wrap">
    <div class="container">
        <div class="row">
            <div class="col-md-5">
                <div class="edu-course-learn-img">
                    <img src="<?php echo base_url();?>/assets/images/learning.png" alt="images">
                </div>
            </div>
            	<?php if(!empty($batch_fecherd)) { ?>
            <div class="col-md-7">
                <div class="edu-cousre-learn-list">
                    <div class="edu-course-heading">
                        <h1><?php echo $batch_fecherd[0]['batch_specification_heading'];?></h1>
                    </div>
                    <ul>
                        <?php 
                        foreach($batch_fecherd as $feat){ 
                        $fetured = json_decode($feat['batch_fecherd']);
                       foreach($fetured as $feated){ ?>
                        <li><img src="<?php echo base_url();?>/assets/images/check.svg"> <?php echo $feated;?></li>

                       <?php } } ?>
                    </ul>
                </div>
            </div>
            <?php } ?>

        </div>
    </div>
</div>
<!-- course learn end -->

	<!----- Recommended Course Section ----->
		
		<!--<div class="edu-course-content-wrap spacer-top spacer-bottom" id="edu-course-accordian">-->
  <!--  <div class="container">-->
  <!--      <div class="edu_heading_wrapper text-center">-->
		<!--	<h4 class="edu_subTitle"><?php $ttd =html_escape($this->common->languageTranslator('ltr_enhance')); echo !empty($frontend_details[0]['sec_crse_sub_heading'])?$frontend_details[0]['sec_crse_sub_heading']: $ttd;?></h4>-->
		<!--	<h4 class="edu_heading"><?php $cttd =html_escape($this->common->languageTranslator('ltr_our_courses')); echo !empty($frontend_details[0]['sec_crse_heading'])?$frontend_details[0]['sec_crse_heading']:$cttd ;?></h4>-->
		<!--	<img src="<?php echo base_url();?>/assets/images/border.png" alt="">-->
		<!--</div>-->
  <!--      <div class="row">-->
  <!--          <div class="col-md-6">-->
  <!--              <div class="edu-course-accordian">-->
                    <?php
    //                 if(!empty($video_lectures)){
	   //         $i=0;
		  //  foreach($video_lectures as $video){
		  //      $like_sub = array('batch','"'.$video['batch'].'"');
		     
		        
		  //       $video_chapter = $this->db_model->custom_slect_query("* FROM `video_lectures` WHERE  `status` = 1 AND `Batch` LIKE '%".$video['batch']."%' ESCAPE '!' GROUP BY `topic`");
		         	
		      
		  //          foreach($video_chapter as $video_chap){
		             
		  //               $video_data = $this->db_model->custom_slect_query("* FROM `video_lectures` WHERE  `topic` = '".$video_chap['topic']."' AND `Batch` LIKE '%".$video_chap['batch']."%' ESCAPE '!'");
		                 
		  //              echo $this->db->last_query();
		  //              print_r($video_data);
		  //             foreach($video_data as $videoData){
		  //       $i++;
			?>
                    <!--<div class="edu-course-accordian-list">-->
                    <!--    <p class="edu-course-accordian-title collapsed" data-toggle="collapse" data-target="#edu-course-accordian<?php echo $i;?>">-->
                    <!--       Subject : <?php echo $videoData['subject']." </br> Chapter : ".$videoData['topic'];?></p>-->
                    <!--    <div id="edu-course-accordian<?php echo $i;?>" class="collapse" data-parent="#edu-course-accordian">-->
                    <!--        <div class="edu-course-accordian-body">-->
                    <!--            <ul>-->
                    <!--                <li>            -->
                                       <?php
                                    //   echo $videoData['title'];
                                    //     echo '<span class="edu-video-preview"><a class="viewVideo btn_view" title="View" data-id="'.$videoData['id'].'" data-url="'.$videoData['url'].'" data-type="'.$videoData['video_type'].'" data-desc="'.$videoData['description'].'"><i class="fa fa-eye"></i></a></span>';
                                        ?>
                                           
                    <!--                    </span>-->
                    <!--                </li>-->
                    <!--            </ul>-->
                    <!--        </div>-->
                           
                    <!--    </div>-->
                    <!--</div>-->
                      <?php
                    //   } } }  }
                      ?>
<!--                </div>-->
<!--            </div>  -->
           
<!--        </div>-->
<!--    </div>-->
<!--</div>  -->

<div class="edu-course-content-wrap spacer-top spacer-bottom" id="edu-course-accordian">
    <div class="container">
        <div class="edu_heading_wrapper text-center">
			<h4 class="edu_subTitle"><?php $ttd =html_escape($this->common->languageTranslator('ltr_enhance')); echo !empty($frontend_details[0]['sec_crse_sub_heading'])?$frontend_details[0]['sec_crse_sub_heading']: $ttd;?></h4>
			<h4 class="edu_heading"><?php $cttd =html_escape($this->common->languageTranslator('ltr_our_courses')); echo !empty($frontend_details[0]['sec_crse_heading'])?$frontend_details[0]['sec_crse_heading']:$cttd ;?></h4>
			<img src="<?php echo base_url();?>/assets/images/border.png" alt="">
		</div>
        <div class="row">
            <div class="col-md-6">

                <div class="edu-course-accordian">

                    <?php
                    $i=0;
                        foreach ($video_lectures as $strSubjectName => $arrStrChapterDetails){
                            ?>
                        <div class="edu-course-accordian-list" >
                            <p class="edu-course-accordian-title collapsed" onclick="collapseVideoList();" data-toggle="collapse" data-target="#edu-course-accordian<?php echo $i;?>">
                            Subject : <?php echo $strSubjectName;?>
                            <i class="icofont-caret-down edu-image-align"></i>
                        </p>
                            <?php
                                $video = 0;
                                foreach($arrStrChapterDetails as $strChapterName => $arrStrVideos){
                                    $video++;
                            ?>
                                    <div id="edu-course-accordian<?php echo $i;?>" class="collapse " data-parent="#edu-course-accordian">
                                        <p class="edu-course-accordian-title collapsed chapter-header" data-toggle="collapse" data-target="#edu-course-video<?php echo $video; ?>">
                                            <?php echo  $strChapterName;?>
                                            <i class="icofont-caret-down edu-image-align"></i>
                                        </p>


                                    </div>
                                    <div id="edu-course-video<?php echo $video;?>" class="collapse">
                                        <ul>
                                            <?php
                                            foreach ($arrStrVideos as $arrStrVideo){ ?>
                                                <li class="edu-list">

                                                    <a class="viewVideo edu-video-link" data-id="<?php echo $arrStrVideo['id'];?>"  data-url="<?php echo $arrStrVideo['url'];?>" data-type="<?php echo $arrStrVideo['video_type'];?>" data-desc="<?php echo $arrStrVideo['description'];?>">
                                                        <i class="icofont-ui-video-play"></i>

                                                        <u>

                                                            <?php
                                                            echo $arrStrVideo['title'];?>
                                                        </u> </a>
                                                    <?php
                                                    echo '<span class="edu-video-preview">
                                                <a class="viewVideo btn_view" title="View" data-id="'.$arrStrVideo['id'].'" data-url="'.$arrStrVideo['url'].'" data-type="'.$arrStrVideo['video_type'].'" data-desc="'.$arrStrVideo['description'].'"><i class="fa fa-eye"></i></a></span>'; ?>

                                                    </span>
                                                </li>
                                            <?php }
                                            ?>

                                        </ul>
                                    </div>

                            <?php } ?>
                        </div>
                            <?php $i++; }
                        ?>

                </div>

            </div>  
           
        </div>
    </div>
</div>  
	
			<!-- course slider start -->
<div class="edu-course-wrap spacer-top edu-course-slider edu-course-bg-wrap">
    <div class="container">
        <div class="edu_heading_wrapper text-center">
			<h4 class="edu_subTitle"><?php $ttd =html_escape($this->common->languageTranslator('ltr_enhance')); echo !empty($frontend_details[0]['sec_crse_sub_heading'])?$frontend_details[0]['sec_crse_sub_heading']: $ttd;?></h4>
			<h4 class="edu_heading"><?php echo html_escape($this->common->languageTranslator('ltr_recod_course'));?></h4>
			<img src="<?php echo base_url();?>assets/images/border.png" alt=""/>
		</div>
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="swiper-container">
                    <div class="swiper-wrapper">
                        	<?php 
					if(!empty($batches)){
								foreach($batches as $value){
									?>
                        <div class="swiper-slide">
                            <div class="edu-course-box">
                                <div class="edu-course-img">
                                    <img src="<?php if(!empty($value['batch_image'])) { echo base_url('uploads\batch_image/').$value['batch_image'] ; }else{ echo base_url('uploads/site_data/'.$site_Details['0']['site_logo']); } ?>" alt="image">
                                    	<?php if(!empty($value['batch_offer_price'])){ ?> <span class="edu-course-offer"><?php echo html_escape($this->common->languageTranslator('ltr_offer')); ?></span> <?php } ?>
                                    <a href="<?php echo base_url('enroll-now/'.$value['id']); ?>" class="edu-enroll-btn"><?php echo html_escape($this->common->languageTranslator('ltr_enroll_now'));?></a>
                                </div> 
                                <div class="edu-course-content">
                                    <a href="<?php echo base_url('courses-details/'.$value['id']); ?>" class="edu-course-title"><?php echo $value['batch_name'];?></a>
                                    <p><?php echo $value['description']; ?></p>
                                    <!--<ul class="edu-course-rating">-->
                                    <!--    <li><i class="fas fa-star"></i></li>-->
                                    <!--    <li><i class="fas fa-star"></i></li>-->
                                    <!--    <li><i class="fas fa-star"></i></li>-->
                                    <!--    <li><i class="fas fa-star"></i></li>-->
                                    <!--    <li><i class="fas fa-star"></i></li>-->
                                    <!--</ul>-->
                                    <h3 class="edu-course-price"><?php if($value['batch_type']==2){ if(!empty($value['batch_offer_price'])){ echo '<s>'.$currency_decimal.' '.$value['batch_price'].'</s> / '.$currency_decimal.' '.$value['batch_offer_price']; }else{ echo $currency_decimal.' '.$value['batch_price'];} }else{ echo "Free";} ?></h3>
                                    <a href="<?php echo base_url('courses-details/'.$value['id']); ?>" class="edu_courses_view"><?php echo html_escape($this->common->languageTranslator('ltr_course_view'));?><i class="fas fa-long-arrow-alt-right"></i></a>
                                </div>
                            </div>
                        </div>
                         <?php } } ?>
                    </div>
                    <div class="swiperTeamPagination"></div>
                </div>
            </div>
           
        </div>
    </div>
</div>
			
		

<!-- view Pop Up Start  -->

<div id="view_video_popup" class="edu_popup_container mfp-hide">
    <div class="edu_popup_wrapper">
        <div class="edu_popup_inner">
            <h4 class="edu_sub_title" id="viewVideoTopic"><?php echo html_escape($this->common->languageTranslator('ltr_view_video'));?></h4>
            <div class="row videoIframeShow">
            </div>
        </div>
    </div>
</div>
