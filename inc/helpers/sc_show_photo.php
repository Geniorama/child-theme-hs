<?php
if(!function_exists('hs_show_photo_func')){
    
    add_shortcode('hs_show_photo', 'hs_show_photo_func');
    
    function hs_show_photo_func($atts){
        if($_GET['id-empresario']){
            $id_empresario = $_GET['id-empresario'];
            $image = get_field('foto_perfil', $id_empresario);
            $id_image = $image['ID'];

            echo wp_get_attachment_image($id_image, 'full');
        }  

    }
}