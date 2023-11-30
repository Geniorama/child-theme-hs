<?php
if(!function_exists('hs_show_name_func')){
    
    add_shortcode('hs_show_name', 'hs_show_name_func');
    
    function hs_show_name_func($atts){        
        if($_GET['id-empresario']){
            $id_empresario = $_GET['id-empresario'];
            $atts = shortcode_atts( array(
                'show' => 'full'
            ), $atts, 'hs_show_name');
    
            $show = $atts['show'];
            $fname = get_field('nombre', $id_empresario);
            $lname = get_field('apellido', $id_empresario);
           
            if($show == "full"){
                echo "<p><span class='hs-fname'>" . $fname . "</span> <span class='hs-lname'>" . $lname . "</span>";
            }
    
            if($show == "fname"){
                echo "<p><span class='hs-fname'>" . $fname . "</span>";
            }
    
            if($show == "fname"){
                echo "<p><span class='hs-lname'>" . $lname . "</span>";
            }
        } 

    }
}