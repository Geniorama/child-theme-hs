<?php

/**
 * SHORTCODE PARA MOSTRAR AGENDA PARA RESERVAR
 * [hs_booking]
 * Página single empresarios
 */
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

    // Pasar la URL de admin-ajax.php al script
    wp_localize_script('booking-js', 'agendarcita', array('ajaxurl' => admin_url('admin-ajax.php')));


    global $post;
    $empresario = get_the_ID();
    $post = get_post($empresario);
    setup_postdata($post);
    $nombre = get_field('nombre');
    $apellido = get_field('apellido');
    $nombre_empresa = get_field('nombre_empresa');
    $cargo = get_field('cargo');
    $horarios_rueda_de_negocios = get_field('horarios_rueda_de_negocios');
    $current_user = wp_get_current_user();

    // Obtener los bloques de horas ya reservados por el usuario
    $reservaciones_realizadas_actualizado = get_user_meta($current_user->ID, 'reservaciones_realizadas', true);

    // Deserializar para obtener el array
    $reservaciones_realizadas_actualizado = unserialize($reservaciones_realizadas_actualizado);
    ob_start();
?>

    <div class="hs-booking-table">
      <div class="hs-booking-title">
        <h5>PROGRAMACIÓN</h5>
        <p>Selecciona la hora de tu agendamiento</p>
      </div>
      <div class="hs-heading">
        <h5><?php the_field('fecha_del_evento', 1369) ?></h5>
      </div>
      <div class="hs-body">
        <?php if ($horarios_rueda_de_negocios) : ?>
          <ul class="hs-body__list">
            <!-- Aquí va el loop de horas -->

            <?php foreach ($horarios_rueda_de_negocios as $indice => $horario) : ?>
              <?php if (strlen($horario['hora_inicio']) > 0 || strlen($horario['hora_fin']) > 0) : ?>
                <?php $bloque_hora_reservado = "{$horario['hora_inicio']} - {$horario['hora_fin']}"; ?>
                <li class="hs-body__list__item">
                  <span class="hs-body__list__item__time">
                    <?php echo $bloque_hora_reservado; ?>
                  </span>

                  <span class="hs-body__list__item__action">
                    <?php if ($horario['agendar']) : ?>
                      <button class="hs-button-booking disabled">
                        NO DISPONIBLE
                      </button>
                    <?php else : ?>
                      <?php if (bloque_hora_disponible_usuario($horario['hora_inicio'], $reservaciones_realizadas_actualizado)) : ?>
                        <button class="hs-button-booking" onclick="alert('Ya tienes una reserva en este mismo horario con otro empresario')">
                        <?php else : ?>
                          <button class="hs-button-booking agendar-button" data-bloque-hora-id="<?php echo $indice ?>" data-id-usuario="<?php echo esc_attr($empresario) ?>">
                          <?php endif; ?>
                          Agendar <i>Hand Shake</i>
                          </button>
                        <?php endif; ?>
                  </span>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>



    <div class="hs-modal-booking">
      <div class="hs-modal-booking__card">
        <hr>
        <div class="hs-card-heading">
          <p>Estás agendando tu cita con</p>
          <h3 class="hs-card-heading__name">
            <!-- Nombre y apellido de empresario -->
            <span class="hs-card-heading__fname"><?php echo esc_attr($nombre) ?></span>
            <span class="hs-card-heading__lname"><?php echo esc_attr($apellido) ?></span>
          </h3>
          <p>
            <!-- Cargo y empresa de empresario -->
            <span><?php echo esc_attr($cargo) ?></span>
            /
            <span><?php echo esc_attr($nombre_empresa) ?></span>
          </p>
        </div>
        <hr>
        <div class="hs-card-body">
          <p class="hs-info-booking">Para el día <span class="hs-date"><?php the_field('fecha_del_evento', 1369) ?></span> a las <span class="hs-time">[HORA]</span></p>
          <p>No podrás agendar otro horario con este mismo empresario</p>
          <p class="hs-text-continue">¿Deseas continuar?</p>
          <div class="hs-modal-action-buttons">
            <button class="hs-button-booking hs-btn-cancel">CANCELAR</button>
            <button class="hs-button-booking hs-btn-continue">CONTINUAR</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      jQuery(document).ready(function($) {
        // Evento de clic en el botón Agendar
        $('.agendar-button').on('click', function() {
          // Obtener el bloque de hora
          var bloqueHora = $(this).closest('.hs-body__list__item').find('.hs-body__list__item__time').text();

          // Actualizar el texto en la modal
          $('.hs-time').text(bloqueHora);
        });
      });
    </script>

<?php
    return ob_get_clean();
  }

  /**
   * Valida si el usuario no tiene ninguna agenda en el bloque de hora que desea reservar
   */
  function bloque_hora_disponible_usuario($bloque_hora_inicio_agenda, $reservaciones_realizadas_actualizado)
  {
    foreach ($reservaciones_realizadas_actualizado as $reserva_realizada) {
      if ($bloque_hora_inicio_agenda === $reserva_realizada['bloque_horas']['hora_inicio']) return true;
    }
    return false;
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
      $empresario_id = sanitize_text_field($_POST['id_usuario']);
      $current_user = wp_get_current_user();

      // Obtener los horarios de la rueda de negocios
      $horarios_rueda_de_negocios = get_field('horarios_rueda_de_negocios', $empresario_id);
      // Obetener todas las reservas de un empresario
      $reservas_del_empresario = get_field('usuarios_agendados', $empresario_id);
      $cantidad_de_reservas = count($reservas_del_empresario);

      // Verificar que el bloque de hora existe
      if (isset($horarios_rueda_de_negocios[$bloque_hora_id])) {
        // Obtener el valor actualizado de reservaciones_realizadas_empresarios
        $reservaciones_realizadas_actualizado = get_user_meta($current_user->ID, 'reservaciones_realizadas', true);

        // Deserializar para obtener el array
        $reservaciones_realizadas_actualizado = unserialize($reservaciones_realizadas_actualizado);

        // Verificar si el usuario ya tiene una reserva con el mismo empresario
        if (!$reservaciones_realizadas_actualizado || !in_array($empresario_id, array_column($reservaciones_realizadas_actualizado, 'empresario_id'))) {
          // Verificar si el usuario ha alcanzado el límite de reservas (por ejemplo, 5)
          $max_reservas = get_field('limite_reservas', 1369);
          if (!$reservaciones_realizadas_actualizado || count($reservaciones_realizadas_actualizado) < $max_reservas) {
            // Actualizar el campo 'agendar' a true para el bloque de hora especificado
            $horarios_rueda_de_negocios[$bloque_hora_id]['agendar'] = true;

            // Guardar el bloque de hora reservado en el perfil del usuario
            $bloque_hora_reservado = $horarios_rueda_de_negocios[$bloque_hora_id];
            $intervalo_tiempo = $bloque_hora_reservado['hora_inicio'] . ' - ' . $bloque_hora_reservado['hora_fin'];

            //Actualizar Datos del usuario en el dashboard del empresario
            $reservas_del_empresario[$cantidad_de_reservas]['id_usuario'] = get_current_user_id();
            $reservas_del_empresario[$cantidad_de_reservas]['nombre_usuario'] = $current_user->display_name;
            $reservas_del_empresario[$cantidad_de_reservas]['correo_usuario'] = $current_user->user_email;
            $reservas_del_empresario[$cantidad_de_reservas]['bloque_de_hora'] = $intervalo_tiempo;


            // Guardar los cambios en la base de datos
            update_field('horarios_rueda_de_negocios', $horarios_rueda_de_negocios, $empresario_id);
            update_field('usuarios_agendados', $reservas_del_empresario, $empresario_id);


            // Nuevo objeto con el id del empresario y el bloque de horas reservado
            $nueva_reservacion = array(
              'empresario_id' => $empresario_id,
              'bloque_horas' => $bloque_hora_reservado,
            );

            // Agregar el nuevo objeto al array
            $reservaciones_realizadas_actualizado[] = $nueva_reservacion;

            // Serializar antes de actualizar el campo
            $reservaciones_serializadas = serialize($reservaciones_realizadas_actualizado);

            // Actualizar el campo reservaciones_realizadas con los nuevos valores serializados
            update_user_meta(get_current_user_id(), 'reservaciones_realizadas', $reservaciones_serializadas);
            wp_die(json_encode(array('message' => 'Éxito', 'type' => 'success')));
          } else {
            // echo 'Error: Has alcanzado el límite de reservas permitidas';
            wp_die(json_encode(array('message' => 'Error: Has alcanzado el límite de reservas permitidas', 'type' => 'error')));
          }
        } else {
          // echo "<script>alert('Error: Ya tienes una reserva con este empresario');</script>";
          wp_die(json_encode(array('message' => 'Error: Ya tienes una reserva con este empresario', 'type' => 'error')));
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
