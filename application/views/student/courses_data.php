<section class="edu_admin_content">
	<div class="sectionHolder edu_admin_right edu_dashboard_wrap">
	  
	  <div class="edu-search-course edu-card-white">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="edu-select-category">
                    <h3>Categories</h3>
                    <ul>
                        <?php
                        foreach($category_data as $cat){ ?>
                        <li class="edu-category-panel"><a href="javascript:;"><?php echo $cat['name'];
                        ?></a><?php $cat_id = $cat['id']; 
                         $sub_data = $this->db_model->select_data('id,name,slug','batch_subcategory use index (id)',array('cat_id'=>$cat_id,'status'=>'1'));?>
                             <?php if(!empty($sub_data)) {?>
                            <ul>
                                <li><a class="subcatData" data-id="<?=$sub_data[0]['id']?>" href="javascript:;"><?php echo $sub_data[0]['name']; ?></a>
                                
                                </li>
                            </ul>
                                 <?php } ?>
                        </li>
                        
                        <?php } ?>
                           <li><a class="subcatData" data-id="0" href="javascript:;">Other</a>
                       
                        </li>
                    </ul>

                </div>
            </div>
            <div class="col-md-4">
                <div class="edu-search-form">
                    <input type="text" id="search_field" name="search_field" placeholder="Search Course">
                    <span class="edu-search-icon">
                       <a href="javascript:;" id="search_button" name="search_button" placeholder="Search Course"> <i class="fas fa-search"></i></a>
                    </span>
                </div> 
            </div>
        </div>
    
	    <div id="search_data_course">
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
					            <li><p><a href="#" class="courses_atc enrollNowSubmit" data-id="<?=$value['id'];?>" data-email="<?=$this->session->userdata('email')?>" data-name="<?=$this->session->userdata('name')?>" data-mobile="<?=$this->session->userdata('contact_no')?>"> <?php echo html_escape($this->common->languageTranslator('ltr_enroll_now'));?> </a></p></li>
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
      </div>
      
      <!--<div class="edu-trending-course">-->
      <!--  <div class="container">-->
      <!--  <div class="edu-course-heading">-->
      <!--      <h1>Trending Course</h1>-->
      <!--  </div>-->
      <!--  <ul class="nav edu-nav-tabs" role="tablist">-->
      <!--      <li class="nav-item">-->
      <!--          <a class="active" id="home-tab" data-toggle="tab" href="#trending" role="tab" aria-selected="true">Trending</a>-->
      <!--      </li>-->
      <!--      <li class="nav-item">-->
      <!--          <a id="profile-tab" data-toggle="tab" href="#free" role="tab" aria-selected="false">Free</a>-->
      <!--      </li>-->
      <!--      <li class="nav-item">-->
      <!--          <a id="contact-tab" data-toggle="tab" href="#new" role="tab" aria-selected="false">New</a>-->
      <!--      </li>-->
      <!--  </ul>-->
   
      <!--  <div class="tab-content">-->
      <!--      <div class="tab-pane fade show active" id="trending" role="tabpanel" aria-labelledby="home-tab">-->
      <!--          <div class="row">-->
                    	<?php
				 	if(!empty($trending_courses)){
							    
				 		foreach($trending_courses as $trend){
								?>
      <!--              <div class="col-lg-3 col-sm-6">-->
      <!--                  <div class="edu-course-box">-->
      <!--                      <div class="edu-course-img">-->
      <!--                          <img src="<?php if(!empty($trend['batch_image'])) { echo base_url('uploads\batch_image/').$trend['batch_image'] ; }else{ echo base_url('uploads/site_data/'.$site_Details['0']['site_logo']); } ?>" alt="image">-->
      <!--                         	<?php if(!empty($trend['batch_offer_price'])){ ?> <span class="edu-course-offer"><?php echo html_escape($this->common->languageTranslator('ltr_offer')); ?></span> <?php } ?>-->
      <!--                          <a href="<?php echo base_url('enroll-now/'.$trend['id']); ?>" class="edu-enroll-btn"><?php echo html_escape($this->common->languageTranslator('ltr_enroll_now'));?></a>-->
      <!--                      </div> -->
      <!--                      <div class="edu-course-content">-->
      <!--                          <a href="javascript:;" class="edu-course-title"><?php echo $trend['batch_name'];?></a>-->
      <!--                          <p><?php echo $trend['description']; ?></p>-->
                                <!--<ul class="edu-course-rating">-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--</ul>-->
      <!--                          <h3 class="edu-course-price"><?php if($trend['batch_type']==2){ if(!empty($trend['batch_offer_price'])){ echo '<s>'.$currency_decimal.' '.$trend['batch_price'].'</s> / '.$currency_decimal.' '.$trend['batch_offer_price']; }else{ echo $currency_decimal.' '.$trend['batch_price'];} }else{ echo "Free";} ?></span></h3>-->
      <!--                          <a href="<?php echo base_url('courses-details/'.$trend['id']); ?>" class="edu_courses_view"><?php echo html_escape($this->common->languageTranslator('ltr_course_view'));?><i class="fas fa-long-arrow-alt-right"></i></a>-->
      <!--                      </div>-->
      <!--                  </div>-->
      <!--              </div>-->
                    
      <!--              <?php } } ?>-->
      <!--                  </div>-->
      <!--              </div>-->
               
      <!--      <div class="tab-pane fade" id="free" role="tabpanel" aria-labelledby="profile-tab">-->
      <!--          <div class="row">-->
                    	<?php
				 	if(!empty($free_courses)){
							    
				 		foreach($free_courses as $free){
								?>
      <!--              <div class="col-lg-3 col-sm-6">-->
      <!--                  <div class="edu-course-box">-->
      <!--                      <div class="edu-course-img">-->
      <!--                          <img src="<?php if(!empty($free['batch_image'])) { echo base_url('uploads\batch_image/').$free['batch_image'] ; }else{ echo base_url('uploads/site_data/'.$site_Details['0']['site_logo']); } ?>" alt="image">-->
      <!--                          	<?php if(!empty($free['batch_offer_price'])){ ?><span class="edu-course-offer"><?php echo html_escape($this->common->languageTranslator('ltr_offer')); ?></span><?php } ?>-->
      <!--                          <a href="<?php echo base_url('enroll-now/'.$free['id']); ?>" class="edu-enroll-btn"><?php echo html_escape($this->common->languageTranslator('ltr_enroll_now'));?></a>-->
      <!--                      </div> -->
      <!--                      <div class="edu-course-content">-->
      <!--                          <a href="<?php echo base_url('courses-details/'.$free['id']); ?>" class="edu-course-title"><?php echo $value['batch_name'];?></a>-->
      <!--                          <p><?php echo $value['description']; ?></p>-->
                                <!--<ul class="edu-course-rating">-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--</ul>-->
      <!--                          <h3 class="edu-course-price"><?php if($free['batch_type']==2){ if(!empty($free['batch_offer_price'])){ echo '<s>'.$currency_decimal.' '.$free['batch_price'].'</s> / '.$currency_decimal.' '.$free['batch_offer_price']; }else{ echo $currency_decimal.' '.$free['batch_price'];} }else{ echo "Free";} ?></span></h3>-->
      <!--                          <a href="<?php echo base_url('courses-details/'.$free['id']); ?>" class="edu_courses_view"><?php echo html_escape($this->common->languageTranslator('ltr_course_view'));?> <i class="fas fa-long-arrow-alt-right"></i></a>-->
      <!--                      </div>-->
      <!--                  </div>-->
      <!--              </div>-->
      <!--              <?php } } ?>-->
      <!--                  </div>-->
      <!--              </div>-->
          
      <!--      <div class="tab-pane fade" id="new" role="tabpanel" aria-labelledby="contact-tab">-->
      <!--          <div class="row">-->
                    	<?php
				 	if(!empty($new_courses)){
							    
				 		foreach($new_courses as $new){
								?>
      <!--              <div class="col-lg-3 col-sm-6">-->
      <!--                  <div class="edu-course-box">-->
      <!--                      <div class="edu-course-img">-->
      <!--                          <img src="<?php if(!empty($new['batch_image'])) { echo base_url('uploads\batch_image/').$new['batch_image'] ; }else{ echo base_url('uploads/site_data/'.$site_Details['0']['site_logo']); } ?>" alt="image">-->
      <!--                          <?php if(!empty($value['batch_offer_price'])){ ?><span class="edu-course-offer"><?php echo html_escape($this->common->languageTranslator('ltr_offer')); ?></span><?php } ?>-->
      <!--                          <a href="<?php echo base_url('enroll-now/'.$new['id']); ?>" class="edu-enroll-btn"><?php echo html_escape($this->common->languageTranslator('ltr_enroll_now'));?></a>-->
      <!--                      </div> -->
      <!--                      <div class="edu-course-content">-->
      <!--                          <a href="javascript:;" class="edu-course-title"><?php echo $new['batch_name'];?></a>-->
      <!--                          <p><?php echo $new['description']; ?></p>-->
                                <!--<ul class="edu-course-rating">-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--    <li><i class="fas fa-star"></i></li>-->
                                <!--</ul>-->
      <!--                          <h3 class="edu-course-price"><?php if($new['batch_type']==2){ if(!empty($new['batch_offer_price'])){ echo '<s>'.$currency_decimal.' '.$new['batch_price'].'</s> / '.$currency_decimal.' '.$new['batch_offer_price']; }else{ echo $currency_decimal.' '.$new['batch_price'];} }else{ echo "Free";} ?></span></h3>-->
      <!--                          <a href="<?php echo base_url('courses-details/'.$new['id']); ?>" class="edu_courses_view"><?php echo html_escape($this->common->languageTranslator('ltr_course_view'));?> <i class="fas fa-long-arrow-alt-right"></i></a>-->
      <!--                      </div>-->
      <!--                  </div>-->
      <!--              </div>-->
                   
      <!--              <?php } } ?>-->
      <!--                  </div>-->
      <!--              </div>-->
      <!--          </div>-->
      <!--      </div>-->
      <!--       </div>-->
      

      
       </div>
</div>  
	</div>
</section> 

