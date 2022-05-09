		<div class="edu_dashboard_widgets mt-4">
    		<div class="row">
                <?php 
                if(!empty($batches)){
                    foreach($batches as $value){
                                    ?>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                    
                    <div class="edu_color_boxes box_left">
                        <div class="edu_dash_box_data">
                            <p><?php echo $value['batch_name'];?></p>
                            <h3><?php if($value['batch_type']==2){ if(!empty($value['batch_offer_price'])){ echo '<s>'.$currency_decimal.' '.$value['batch_price'].'</s> / '.$currency_decimal.' '.$value['batch_offer_price']; }else{ echo $currency_decimal.' '.$value['batch_price'];} }else{ echo html_escape($this->common->languageTranslator('ltr_free'));} ?></h3>
                        </div>
                        <div class="edu_dash_box_icon">
                            <img src="<?php if(!empty($value['batch_image'])) { echo base_url('uploads\batch_image/').$value['batch_image'] ; }else{ echo base_url('uploads/site_data/'.$site_Details['0']['site_logo']); } ?>" alt="image">
                        </div>
                        <div class="edu_dash_info">
    					    <ul>
					            <li><p><a href="<?php echo base_url('courses-details/'.$value['id']); ?>" class="edu_courses_view mt-2" target="_blank"><?php echo html_escape($this->common->languageTranslator('ltr_course_view'));?> </a> </p></li>
					            <li><p><a href="#" class="courses_atc enrollNowSubmit" data-id="<?=$value['id'];?>" data-email="<?=$this->session->userdata('email')?>" data-name="<?=$this->session->userdata('name')?>" data-mobile="<?=$this->session->userdata('mobile')?>"> <?php echo html_escape($this->common->languageTranslator('ltr_enroll_now'));?> </a></p></li>
					        <ul>
					    </div>
                    </div>
                    
                </div>
            <?php }
            }else{ echo '<section class="edu_admin_content">
                        <div class="edu_admin_right sectionHolder edu_add_users">
                            <div class="edu_admin_informationdiv edu_main_wrapper">
                                <div class="eac_text eac_page_re">'.html_escape($this->common->languageTranslator('ltr_no_course_result')).'</div>
                            </div>
                        </div>
                    </section>';
                    
                }?>
              
               
        
    		</div>
        </div>