<?php

if(!function_exists('hs_calendly_booking_func')){
    
    add_shortcode( 'hs_calendly_booking', 'hs_calendly_booking_func');
    
    function hs_calendly_booking_func($atts){
        $atts = shortcode_atts( array(
            'id-suscriptor' => ''
        ), $atts, 'hs_calendly_booking');

        // Validamos si el usuario está logueado
        if(!is_user_logged_in()){
            return false;
        }


        // Carga los estilos CSS
        wp_enqueue_style('table-hs', get_stylesheet_directory_uri() . '/inc/hs-booking/assets/style.css');

        //Carga JS
        wp_enqueue_script( 'booking-js', get_stylesheet_directory_uri() . '/inc/hs-booking/assets/main.js', array('jquery'), '1.0', true);

        ob_start();
        ?>
        
        <div class="hs-booking-table">
            <div class="hs-booking-title">
                <h5>PROGRAMACIÓN</h5>
                <p>Selecciona la hora de tu agendamiento</p>
            </div>
            <div class="hs-heading">
                <h5>5 DE DICIEMBRE DE 2023</h5>
            </div>
            <div class="hs-body">
                <ul class="hs-body__list">
                    <!-- Aquí va el loop de horas -->
                    <li class="hs-body__list__item">
                        <span class="hs-body__list__item__name">
                            Mauricio Sarmiento
                        </span>
                        <span class="hs-body__list__item__time">
                            2:00 p.m a 3:00 p.m
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }   
}