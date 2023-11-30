<?php

if (!function_exists('hs_booking_func')) {

    add_shortcode('hs_booking', 'hs_booking_func');

    function hs_booking_func($atts)
    {
        $atts = shortcode_atts(array(
            'id-empresario' => ''
        ), $atts, 'hs_booking');

        // Validamos si el usuario está logueado
        if (!is_user_logged_in()) {
            return false;
        }


        // Carga los estilos CSS
        wp_enqueue_style('table-hs', get_stylesheet_directory_uri() . '/inc/hs-booking/assets/style.css');

        //Carga JS
        wp_enqueue_script('booking-js', get_stylesheet_directory_uri() . '/inc/hs-booking/assets/main.js', array('jquery'), '1.0', true);

        wp_enqueue_script('agendarcita-script', get_stylesheet_directory_uri() . '/inc/hs-booking/assets/agendarcita.js', array('jquery'), null, true);

        // Pasar la URL de admin-ajax.php al script
        wp_localize_script('agendarcita-script', 'agendarcita', array('ajaxurl' => admin_url('admin-ajax.php')));


        global $post;
        $usuario = get_the_ID();
        $post = get_post($usuario);
        setup_postdata($post);
        $nombre = get_field('nombre');
        $apellido = get_field('apellido');
        $nombre_empresa = get_field('nombre_empresa');
        $horarios_rueda_de_negocios = get_field('horarios_rueda_de_negocios');
        ob_start();
?>

        <div class="hs-modal-booking">
            <div class="hs-modal-booking__card">
                <hr>
                <div class="hs-card-heading">
                    <p>Estás agendando tu cita con</p>
                    <h3 class="hs-card-heading__name">
                        <!-- Nombre y apellido de empresario -->
                        <span class="hs-card-heading__fname">NOMBRE</span>
                        <span class="hs-card-heading__lname">APELLIDO</span>
                    </h3>
                    <p>
                        <!-- Cargo y empresa de empresario -->
                        <span>Cargo</span>
                        /
                        <span>Empresa</span>
                    </p>
                </div>
                <hr>
                <div class="hs-card-body">
                    <p class="hs-info-booking">Para el día <span class="hs-date">5 DE DICIEMBRE DE 2023</span> a las <span class="hs-time">[HORA]</span></p>
                    <p>No podrás agendar otro horario con este mismo empresario</p>
                    <p class="hs-text-continue">¿Deseas continuar?</p>
                    <div class="hs-modal-action-buttons">
                        <button class="hs-button-booking hs-btn-cancel">CANCELAR</button>
                        <button class="hs-button-booking hs-btn-continue">CONTINUAR</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="hs-booking-table">
            <div class="hs-booking-title">
                <h5>PROGRAMACIÓN</h5>
                <p>Selecciona la hora de tu agendamiento</p>
            </div>
            <div class="hs-heading">
                <h5>5 DE DICIEMBRE DE 2023</h5>
            </div>
            <div class="hs-body">
                <?php if ($horarios_rueda_de_negocios) : ?>
                    <ul class="hs-body__list">
                        <!-- Aquí va el loop de horas -->
                        <?php foreach ($horarios_rueda_de_negocios as $indice => $horario) : ?>
                            <li class="hs-body__list__item">
                                <span class="hs-body__list__item__time">
                                    <?php echo $horario['hora_inicio']; ?> - <?php echo $horario['hora_fin']; ?>
                                </span>

                                <span class="hs-body__list__item__action">
                                    <?php if ($horario['agendar']) : ?>
                                        <button class="hs-button-booking disabled">
                                            NO DISPONIBLE
                                        </button>
                                    <?php else : ?>
                                        <button class="hs-button-booking agendar-button" data-bloque-hora-id="<?php echo $indice ?>" data-id-usuario="<?php echo esc_attr($usuario) ?>">
                                            Agendar <i>Hand Shake</i>
                                        </button>
                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
<?php
        return ob_get_clean();
    }


    /**
     * Nueva función para manejar la reservación del bloque de hora
     * Al reservar un bloque de hora de un empresario, éste bloque 
     * queda como no disponible para este empresario.
     * Y al usuario, se le añaden los datos de la reserva en su usermeta
     */
    function reservar_bloque_hora()
    {
        if (isset($_POST['bloque_hora_id'], $_POST['id_usuario'])) {
            $bloque_hora_id = sanitize_text_field($_POST['bloque_hora_id']);
            $usuario_id = sanitize_text_field($_POST['id_usuario']);

            // Obtener los horarios de la rueda de negocios
            $horarios_rueda_de_negocios = get_field('horarios_rueda_de_negocios', $usuario_id);

            // Verificar que el bloque de hora existe
            if (isset($horarios_rueda_de_negocios[$bloque_hora_id])) {
                // Obtener el valor actualizado de reservaciones_realizadas_empresarios
                $reservaciones_realizadas_actualizado = get_user_meta(get_current_user_id(), 'reservaciones_realizadas', true);

                // Deserializar para obtener el array
                $reservaciones_realizadas_actualizado = unserialize($reservaciones_realizadas_actualizado);

                // Verificar si el usuario ya tiene una reserva con el mismo empresario
                if (!$reservaciones_realizadas_actualizado || !in_array($usuario_id, array_column($reservaciones_realizadas_actualizado, 'empresario_id'))) {
                    // Actualizar el campo 'agendar' a true para el bloque de hora especificado
                    $horarios_rueda_de_negocios[$bloque_hora_id]['agendar'] = true;

                    // Guardar los cambios en la base de datos
                    update_field('horarios_rueda_de_negocios', $horarios_rueda_de_negocios, $usuario_id);

                    // Guardar el bloque de hora reservado en el perfil del usuario
                    $bloque_hora_reservado = $horarios_rueda_de_negocios[$bloque_hora_id];

                    // Nuevo objeto con el id del empresario y el bloque de horas reservado
                    $nueva_reservacion = array(
                        'empresario_id' => $usuario_id,
                        'bloque_horas' => $bloque_hora_reservado,
                    );

                    // Agregar el nuevo objeto al array
                    $reservaciones_realizadas_actualizado[] = $nueva_reservacion;

                    // Serializar antes de actualizar el campo
                    $reservaciones_serializadas = serialize($reservaciones_realizadas_actualizado);

                    // Actualizar el campo reservaciones_realizadas con los nuevos valores serializados
                    update_user_meta(get_current_user_id(), 'reservaciones_realizadas', $reservaciones_serializadas);

                    echo 'Éxito'; // Puedes enviar cualquier respuesta que desees de vuelta al frontend
                } else {
                    echo 'Error: Ya tienes una reserva con este empresario';
                }
            } else {
                echo 'Error: Bloque de hora no encontrado';
            }
        } else {
            echo 'Error: Datos de bloque de hora no recibidos';
        }
        die();
    }

    add_action('wp_ajax_reservar_bloque_hora', 'reservar_bloque_hora');
    add_action('wp_ajax_nopriv_reservar_bloque_hora', 'reservar_bloque_hora');
}
