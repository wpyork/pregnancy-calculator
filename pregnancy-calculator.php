<?php
/**
 * 
 * @package           pregnancy-calculator
 * @author            WP York <wpyork143@gmail.com>
 * @license           GPL-2.0-or-later
 * 
 * Plugin Name: LMP Pregnancy Calculator
 * Plugin URI: https://github.com/wpyork/pregnancy-calculator/
 * Description: Pregnancy Calculator
 * Author: wpyork, lincolndu
 * Author URI: https://profiles.wordpress.org/wpyork/
 * Version: 1.0.0
 * 
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Text Domain:       pregnancy-calculator
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 *
*/


if(!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * our plugin constant
* */
define( 'WY_PREG_FILE', __FILE__ );
define( 'WY_PREG_PLUGIN_PATH', __DIR__ );
define( 'WY_PREG_BASENAME', plugin_basename( WY_PREG_FILE ) );
define( 'WY_PREG_DIR', plugin_dir_url( WY_PREG_FILE ) );
define( 'WY_PREG_PATH', plugin_dir_path( WY_PREG_FILE ) );

/**
 * YorkLmpPregnancyCalc class for better code organization
* */

final class YorkLmpPregnancyCalc {

  /**
   * Construct method 
   * @version 1.0.0
   * @author WP York
  * */

  public function __construct ( ) 
  {
    add_filter( 'plugin_action_links_' . WY_PREG_BASENAME, array( $this, 'action_links' ) ); 
    add_action( 'plugins_loaded', array( $this, 'localization_setup' ) ); /*Localize our plugin*/
    /**
     * Style load 
     * */
    add_action( 'wp_print_styles', array( $this, 'preg_calc_plugin_style') );

    /*Plugin shortcode*/
    add_shortcode( 'pregnancy_calc', array( $this, 'CT_Pregnancy_Calculator') );

    /** 
     * Settings*/
    add_action( "admin_init", array( $this,'wypc_settings_init') );

  }

  public function wypc_settings_init() 
  {
      add_settings_section('wypc_section',__('Pregnancy Calculator','pregnancy-calculator'),array( $this, 'wypc_section_callback'),'general');

      add_settings_field( 'wypc_heading', __( 'Plugin Heading', 'pregnancy-calculator' ), array( $this, 'wypc_display_field'), 'general','wypc_section',array('wypc_heading') );

      register_setting( 'general', 'wypc_heading', array( 'sanitize_callback' => 'esc_attr' ) );
  }

  public function wypc_section_callback()
  {
      echo "<p id='pregnancy_settings'>".__('Settings for Pregnancy Calculator plugin','pregnancy-calculator')."</p>";
      echo '<span style="display:block;font-family:monospace;white-space:pre;">
      /** 
        * Plugin shortcode is "pregnancy_calc"
        * show plugin functionality with this shortcode
        * just follow below code in php block
      */
      
      echo do_shortcode("[pregnancy_calc]");

      /** 
        * Your page or post Gutenberg editor
      */

      [\'pregnancy_calc\']
      </span>';
  }

  public function wypc_display_field($args)
  {
      $option = get_option($args[0]);
      if ( $args[0] === 'wypc_heading' ) {
        $option = esc_html('Pregnancy Calculator', 'pregnancy-calculator');
      }
      printf( "<input type='text' id='%s' name='%s' value='%s' class='regular-text' />", $args[0], $args[0], $option );
  }

  /**
   * Load Design file
   * @author wpyork
   * @version 1.0.0
  * */

  public function preg_calc_plugin_style() 
  {
    wp_enqueue_style( 'pregnancy-calculator', WY_PREG_DIR . 'style.css',[], '1.0.0');
  }

  /**
   * */

  public function CT_Pregnancy_Calculator() 
  {
    $menstrual_raw  = sanitize_text_field( $_GET['menstrual'] );
    $conception_raw = sanitize_text_field( $_GET['conception'] );
    $duedate_raw    = sanitize_text_field( $_GET['duedate'] );
    $cycle_raw      = sanitize_text_field( $_GET['cycle'] );
    $wypc_heading   = sanitize_text_field( get_option('wypc_heading') ) ? : 'Pregnancy Calculator';

      if( $menstrual_raw ) {
        $menstrual = $menstrual_raw;
      }

      if( $conception_raw ) {
        $conception = $conception_raw;
      }

      if( $duedate_raw ) {
        $duedate = $duedate_raw;
      }

      if( $cycle_raw ) {
        $cycle = number_format($cycle_raw);
      }

      if( isset($duedate) ) {
        $ov_date = 14;
        $due_date = strtotime($duedate);
        $conception = date('Y-m-d', strtotime('-266 day', strtotime($duedate)));
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
      <h3 class="black satisfy develop text-center"> '.$wypc_heading.' </h3>
      <div class="col-lg-12 col-sm-12">
        <label for="calctype" class="form-label"> '.esc_html('Calculate Based On:', 'pregnancy-calculator').' </label>
          <select class="form-select w-50" name="calctype" id="cal_select">
            <option value="period">'.esc_html('Last Period', 'pregnancy-calculator').'</option>
            <option value="conception">'.esc_html('Conception Date', 'pregnancy-calculator').'</option>
            <option value="duedate">'.esc_html('Due Date', 'pregnancy-calculator').'</option>
          </select>
      </div>';

      /*period form*/
      $html .=' <form onsubmit="return pregnancyCalc(this);" action="" class="preg-calc form-horizontal period" >
          
          <!-- Begin Form -->
          <div id="preg_calc_tool">
            <div class="panel panel-info">
              <div class="panel-body">
                <div class="form-group">
                  <label for="menstrual" class="control-label mens_label col-sm-12 col-lg-6"> '.esc_html('First Day of Last Menstrual Period', 'pregnancy-calculator').'  <strong class="required text-danger">*</strong>
                  </label>
                  <script>
                    function changevalue(a){
                      jQuery("#"+a).attr("value","");
                      jQuery("#"+a).removeClass("hint-value");
                    }
                    function unhide(a,b){ jQuery("#"+a).attr("class",b); }
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
                    '.esc_html('Average Length of Cycles', 'pregnancy-calculator').'
                    </label>
                  </div>
                  <div class="col-sm-12 col-lg-6">
                    <input name="cycle" id="cycle" value="'.($cycle?:28).'" class="form-control cycle" min="0" max="50" type="number" />
                  </div>
                  <!--col-sm-7-->

                </div> <!--form-group-->
                <div class="col-12 my-3">
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
        <form onsubmit="return pregnancyCalc(this);" action="" class="preg-calc form-horizontal duedate" >
          
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
                      </div> <!--date-->
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
    <form onsubmit="return pregnancyCalc(this);" action="" class="preg-calc form-horizontal conception">
      
      <!-- Begin Form -->
      <div id="preg_calc_tool">
        <div class="panel panel-info">
          <div class="panel-body">
            <div class="form-group">
              <label for="menstrual" class="control-label concept col-sm-4">
                Your Conception Date <strong class="required text-danger"> * </strong> 
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
      $now_stamp    = current_time('U');
      $full_days    = ($now_stamp - $menstrual_stamp)/86400;
      $weeks_preg   = floor($full_days/7);
      $percent      = ceil(($full_days/280)*100);
      $mos_preg     = floor($weeks_preg/4);
      $leftover_days= $full_days%7;
          
      $html .= '<div class="pregcalc">
          <div class="divider"></div>
          <div class="result_area text-center">
            <h2 class="darkgray upper">Your baby is due:</h2>
          
            <p class="calendar"><span class="month satisfy text-white"> '.date('M', $due_date).' </span><span class="day satisfy blue">'.date('d', $due_date).'</span> </p>
          
          <div class="black satisfy currentwk">You are currently</div>
          <div class="purple weeks">'.$weeks_preg.'</div>
          <div class="upper pink wkspreg bebas">weeks pregnant</div>
          <p class="conception pink">Estimated Date of Conception: '.date('M d, Y', $conception).'</p>
          
          <p class="purple wksdays">('.$weeks_preg.' weeks '.$leftover_days.' days or '.$mos_preg.' months)</p>

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

    /**
     * Show action links on the plugin screen
     *
     * @param mixed $links
     * @return array
    */
    public function action_links( $links ) 
    {
        if ( is_network_admin() ) {
            return array_merge(
            [
                '<a href="' . network_admin_url( 'options-general.php#pregnancy_settings' ) . '">' . __( 'Settings', 'pregnancy-calculator' ) . '</a>',
                '<a href="' . esc_url( 'https://wordpress.org/support/plugin/lmp-pregnancy-calculator/reviews/#new-post' ) . '">' . __( 'Review', 'pregnancy-calculator' ) . '</a>',
                '<a href="' . esc_url( 'https://wordpress.org/support/plugin/lmp-pregnancy-calculator/' ) . '">' . __( 'Support', 'pregnancy-calculator' ) . '</a>'
            ], $links );
        } elseif ( ! is_network_admin() ) {
            return array_merge(
            [
                '<a href="' . admin_url( 'options-general.php#pregnancy_settings' ) . '">' . __( 'Settings', 'pregnancy-calculator' ) . '</a>',
                '<a href="' . esc_url( 'https://wordpress.org/support/plugin/lmp-pregnancy-calculator/reviews/#new-post' ) . '">' . __( 'Review', 'pregnancy-calculator' ) . '</a>',
                '<a href="' . esc_url( 'https://wordpress.org/support/plugin/lmp-pregnancy-calculator/' ) . '">' . __( 'Support', 'pregnancy-calculator' ) . '</a>'
            ], $links );
        }

    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() 
    {
        load_plugin_textdomain( 'pregnancy-calculator', false, dirname( WY_PREG_BASENAME ) . '/languages/' );
    }
}

new YorkLmpPregnancyCalc();


