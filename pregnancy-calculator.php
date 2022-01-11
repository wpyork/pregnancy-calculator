<?php
/**
 * Plugin Name: LMP Pregnancy Calculator
 * Plugin URI: https://github.com/wpyork/pregnancy-calculator/
 * Description: Pregnancy Calculator
 * Author: wpyork, lincolndu
 * Author URI: https://profiles.wordpress.org/wpyork/
 * Version: 1.0.0
 */

function ct_pregnancy_calculator_plugin_stylesheet() {
  wp_enqueue_style('pregnancy-calculator', '/wp-content/plugins/pregnancy-calculator/style.css',[], time());
}
add_action( 'wp_print_styles', 'ct_pregnancy_calculator_plugin_stylesheet' );

function CT_Pregnancy_Calculator() {
  $dir = plugins_url();

  if(isset($_GET['menstrual'])) {
    $menstrual = $_GET['menstrual'];
  }

  if(isset($_GET['conception'])) {
    $conception = $_GET['conception'];
  }

  if(isset($_GET['duedate'])) {
    $duedate = $_GET['duedate'];
  }

  if(isset($_GET['cycle'])) {
    $cycle = $_GET['cycle'];
    $cycle = number_format($cycle);
  }

  if(isset($duedate)) {
    $ov_date = 14;
    $due_date = strtotime($duedate);
    $conception = date('Y-m-d', strtotime('-266 day', strtotime($duedate)));
    //$conception = strtotime($due_date,'-266 days');
    $menstrual_stamp = strtotime($conception,'-'.$ov_date.' days');
  }

  if(isset($conception)) {
    $ov_date = 14;
    $conception = strtotime($conception);
    $menstrual_stamp = strtotime('-'.$ov_date.' days', $conception);
    $due_date = strtotime('+266 days', $conception);
  }

  if(isset($cycle)) {
    $ov_date = $cycle-14;
    $menstrual_stamp = strtotime($menstrual);
    $fert_start = strtotime('+8 days', $menstrual_stamp);
    $fert_end = strtotime('+18 days', $menstrual_stamp);
    $conception = strtotime('+'.$ov_date.' days', $menstrual_stamp);
    $due_date = strtotime('+266 days', $conception);
  }

  $html ='
  <div class="pc-form">
  <h3 class="black satisfy develop text-center">Pregnancy Calendar and Due Date Calculator</h3>
  <div class="col-lg-12 col-sm-12">
    <label for="calctype" class="form-label"> Calculate Based On: </label>
      <select class="form-select" name="calctype" id="cal_select">
        <option value="period">Last Period</option>
        <option value="conception">Conception Date</option>
        <option value="duedate">Due Date</option>
      </select>
  </div>';

  /*period form*/
    $html .='
    <form onsubmit="return pregnancyCalc(this);" action="" class="preg-calc form-horizontal period" role="form">
      
      <!-- Begin Form -->

      <div id="preg_calc_tool">

                <div class="panel panel-info">
                    
                  <div class="panel-body">

                    <div class="form-group">
                      <label for="menstrual" class="control-label mens_label col-sm-12 col-lg-6">
                        First Day of Last Menstrual Period<strong class="required text-danger">*</strong>
                            </label>
                            <script>
                                function changevalue(a){
                                  jQuery("#"+a).attr("value","");
                                  jQuery("#"+a).removeClass("hint-value");
                              }
                              function unhide(a,b){
                                    jQuery("#"+a).attr("class",b);
                                }
                            </script>
                            <div class="col-sm-12 col-lg-6">
                              <div class="input-group date" id="datetimepicker1">
                                <input type="date" id="menstrual" class="form-control" name="menstrual" value="'.$menstrual.'" class="medium" maxlength="10" min="'.date("Y-m-d", strtotime('-1 year')).'" max="'.date("Y-m-d").'">
                              </div><!--date-->
                            </div><!--col-sm-7-->
                    </div><!--form-group-->
           
                    <div class="form-group">
                      <div class="col-sm-12 col-lg-6 marginr-10">
                        <label for="cycle" class="control-label">
                          Average Length of Cycles
                        </label>
                      </div>
                      <div class="col-sm-12 col-lg-6">
                        <input name="cycle" id="cycle" value="28" class="form-control cycle" min="0" max="31" type="number" />
                      </div>
                      <!--col-sm-7-->

                    </div> <!--form-group-->
                    <div class="col-md-10 my-3">
                      <div class="help-block alert alert-info"> <p> From first day of your period to the first day of your next period. Ranges from: 22 to 44. Default = 28 <em>Optional:</em> Leave 28 if unsure. </p> </div>
                    </div>
                    <div class="panel-footer panel-info">
                      <button class="btn btn-warning btn-block" value="Calculate!" type="submit" onclick="unhide("results","normal")"><i class="fa fa-calendar fa-lg"></i>  Get The Date!</button> 
                    </div><!--panel-footer-->

                  </div><!--panel-body-->

                </div><!--panel-->
              </div><!--preg_calc_tool-->
        
    </form>';

    /*due date*/
    $html .='
    <form onsubmit="return pregnancyCalc(this);" action="" class="preg-calc form-horizontal duedate" role="form">
      
      <!-- Begin Form -->

      <div id="preg_calc_tool">

                <div class="panel panel-info">
                    
                    <div class="panel-body">

                      <div class="form-group">
                        <label for="menstrual" class="control-label due col-sm-4">
                          Your Due Date <strong class="required text-danger">*</strong>
                        </label>
                          <script>
                            function changevalue(a){
                                jQuery("#"+a).attr("value","");
                                jQuery("#"+a).removeClass("hint-value");
                            }
                            function unhide(a,b){ jQuery("#"+a).attr("class",b); }
                          </script>
                          <div class="col-sm-7">
                            <div class="input-group date" id="datetimepicker1">
                              <input type="date" id="duedate" class="form-control" name="duedate" value="'.$duedate.'" class="medium" min="'.date("Y-m-d", strtotime('-1 year')).'" min="'.date("Y-m-d", strtotime('-1 year')).'" max="'.date("Y-m-d").'">
                                
                            </div><!--date-->
                          </div><!--col-sm-7-->
                        </div><!--form-group-->

                      <div class="panel-footer panel-info">
                        <button class="btn btn-warning btn-block" value="Calculate!" type="submit" onclick="unhide("results","normal")"><i class="fa fa-calendar fa-lg"></i>  Calculate!</button> 
                      </div><!--panel-info-->
                    </div><!--panel-body-->
                </div><!--panel-->

      </div> <!--preg_calc_tool-->
        
    </form>';

/*conception*/
$html .='
<form onsubmit="return pregnancyCalc(this);" action="" class="preg-calc form-horizontal conception" role="form">
  
  <!-- Begin Form -->

  <div id="preg_calc_tool">

            <div class="panel panel-info">
                
                <div class="panel-body">

                <div class="form-group">
                  <label for="menstrual" class="control-label concept col-sm-4">
                    Your Conception Date<strong class="required text-danger">*</strong>
                        </label>
                        <script>
                            function changevalue(a){
                              jQuery("#"+a).attr("value","");
                              jQuery("#"+a).removeClass("hint-value");
                          }
                          function unhide(a,b){ jQuery("#"+a).attr("class",b); }
                        </script>
                        <div class="col-sm-7">
                            <div class="input-group date" id="datetimepicker1">
                                <input type="date" id="conception" class="form-control" name="conception" value="'.$conception.'" class="medium" min="'.date("Y-m-d", strtotime('-1 year')).'" max="'.date("Y-m-d").'">
                                
                            </div>
                        </div>
                </div>
         
                </div>

                <div class="panel-footer panel-info">
                    <button class="btn btn-warning btn-block" value="Calculate!" type="submit" onclick="unhide("results","normal")"><i class="fa fa-calendar fa-lg"></i>  Calculate!</button> 
                </div>

            </div>

  </div>
    
</form>
<div class="alert alert-warning">This is not a diagnosis. The calculations that are provided are estimates based on averages.</div>
</div>

<script>
  jQuery(document).ready(function($){
    $(".preg-calc").hide();
    $(".period").show();
    $("#cal_select").change(function(){
      $(".preg-calc").hide();
      $("." + $(this).val()).show();
    });
  });
</script>';

  if( $menstrual || $conception || $due_date ) {
    $mens_month = substr($menstrual, 5, 2);
    $mens_day = substr($menstrual, 8, 2);
    $mens_year = substr($menstrual, 0, 4);
    //$now_stamp = strtotime("now");
    $now_stamp = current_time('U');
    $days_preg = ($now_stamp - $conception)/86400;
    $full_days = ($now_stamp - $menstrual_stamp)/86400;
    $weeks_preg = floor($full_days/7);
    $percent = ceil(($full_days/280)*100);
    $mos_preg = floor($weeks_preg/4);
    $wks_leftover_preg = $weeks_preg%4;
    $leftover_days = $full_days%7;
    
    $html .= '<div class="pregcalc">
      <div class="divider"></div>
      <div class="result_area text-center">
        <h2 class="darkgray upper">Your baby is due:</h2>
      
        <p class="calendar"><span class="month satisfy"> '.date('M', $due_date).' </span><span class="day satisfy blue">'.date('d', $due_date).'</span><!--<span class="year satisfy">'.date('Y', $due_date).'</span>--></p>
      
      <div class="black satisfy currentwk">You are currently</div>
      <div class="purple weeks">'.$weeks_preg.'</div>
      <div class="upper pink wkspreg bebas">weeks pregnant</div>
      <p class="conception pink">Estimated Date of Conception: '.date('M d, Y', $conception).'</p>
      
      <p class="purple wksdays">('.$weeks_preg.' weeks '.$leftover_days.' days or '.$mos_preg.' months)</p>

      <!--<p><a class="darkgray ubuntu" href="#week'.$weeks_preg.'">Jump to details about week '.$weeks_preg.' ></a></p>-->
      <small><i>This is based on the cycle length provided, not an average cycle length of 28 days, however it is still an estimate.</i></small>
      <div class="divider"></div>

      <div class="pink upper bebas progress_head">Progress</div>

      <p class="black satisfy percent">You are '.$percent.'% of the way through your pregnancy.</p>
      <p class="progress" style="display: inline-block;border: 1px solid #000;width: 200px;padding-left: 5px;height: 40px;background: -webkit-linear-gradient(left, rgb(220, 107, 136) '.$percent.'%, transparent 0%);
      background: -moz-linear-gradient(left, rgb(220, 107, 136) '.$percent.'%, transparent 0%);
      background: -o-linear-gradient(left, rgb(220, 107, 136) '.$percent.'%, transparent 0%);
      background: -ms-linear-gradient(left, rgb(220, 107, 136) '.$percent.'%, transparent 0%);
      background: linear-gradient(left, rgb(220, 107, 136) '.$percent.'%, transparent 0%);">'.$percent.'%<span class="heart"></span></p>

      <div class="divider"></div>
      </div>
    </div>';
    
  }

    return $html;
}


add_shortcode( 'ctpregcalc', 'CT_Pregnancy_Calculator' );

