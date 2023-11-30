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

        $reservadas_serializadas = get_user_meta(get_current_user_id(), 'reservaciones_realizadas', true);

	    $reservadas_deserializadas = unserialize($reservadas_serializadas);

        ob_start();
        if($reservadas_deserializadas && is_array($reservadas_deserializadas)):
        ?>
        <div class="hs-booking-table">
            <div class="hs-booking-title">
                <h5>PROGRAMACIÓN</h5>
            </div>
            <div class="hs-heading">
                <h5>5 DE DICIEMBRE DE 2023</h5>
            </div>
            <div class="hs-body">
                <ul class="hs-body__list">
                    <!-- Aquí va el loop de horas -->
                    <?php foreach($reservadas_deserializadas as $reservacion): 
                        $empresario_id = $reservacion['empresario_id'];
                        $bloque_horas = $reservacion['bloque_horas'];
            
                        $empresario = get_post($empresario_id);
                        $nombre_empresario = ($empresario) ? get_the_title($empresario) : 'Empresario no encontrado';
            
                        $rango_horas_reservadas = '';
                        if (is_array($bloque_horas) && isset($bloque_horas['hora_inicio'], $bloque_horas['hora_fin'])) {
                            $rango_horas_reservadas = $bloque_horas['hora_inicio'] . ' - ' . $bloque_horas['hora_fin'];
                        }

                    ?>
                    <li class="hs-body__list__item">
                        <span class="hs-body__list__item__name">
                            <?php echo esc_html($nombre_empresario); ?>
                        </span>
                        <span class="hs-body__list__item__time">
                            <?php echo esc_html($rango_horas_reservadas); ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php
        endif;
        return ob_get_clean();
    }   
}