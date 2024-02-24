<?php

/**
 * SHORTCODE PARA MOSTRAR CALENDARIO
 * [hs_calendly_booking]
 * Página Mis Handshakes
 */


if (!function_exists('hs_calendly_booking_func')) {

  add_shortcode('hs_calendly_booking', 'hs_calendly_booking_func');

  function hs_calendly_booking_func()
  {
    // Validamos si el usuario está logueado
    if (!is_user_logged_in()) {
      return false;
    }

    $current_user = get_current_user_id();

    // Carga los estilos CSS
    wp_enqueue_style('table-hs', get_stylesheet_directory_uri() . '/inc/hs-booking/assets/style.css');

    //Carga JS
    wp_enqueue_script('mainjs', get_stylesheet_directory_uri() . '/inc/hs-booking/assets/main.js', array('jquery'), '1.0', true);

    // Pasar la URL de admin-ajax.php al script
    wp_localize_script('mainjs', 'eliminarcita', array('ajaxurl' => admin_url('admin-ajax.php')));

    $reservadas_serializadas = get_user_meta($current_user, 'reservaciones_realizadas', true);

    $reservadas_deserializadas = unserialize($reservadas_serializadas);

    ob_start();
?>
    <div class="hs-booking-table">
      <div class="hs-booking-title">
        <h5>PROGRAMACIÓN</h5>
      </div>
      <div class="hs-heading">
        <h5><?php the_field('fecha_del_evento', 1369) ?></h5>
      </div>
      <div class="hs-body">
        <?php if ($reservadas_deserializadas && is_array($reservadas_deserializadas) && count($reservadas_deserializadas) > 0) :
          // Ordenar el array de reservaciones por hora de inicio
          usort($reservadas_deserializadas, 'comparar_horas');
        ?>
          <ul class="hs-body__list">
            <!-- Aquí va el loop de horas -->
            <pre>
                        <?php var_dump($reservadas_deserializadas) ?>
                        </pre>
            <?php // Imprimir los elementos del array ordenados
            foreach ($reservadas_deserializadas as $reservacion) :
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
                  <button class="hs-button-booking hs-eliminar-reserva" data-reserva="<?php echo esc_attr(json_encode($reservacion)); ?>" data-reservas-realizadas="<?php echo esc_attr(json_encode($reservadas_deserializadas)) ?>">
                    Eliminar Cita
                  </button>
                  <?php echo esc_html($nombre_empresario); ?>
                </span>
                <span class="hs-body__list__item__time">
                  <?php echo esc_html($rango_horas_reservadas); ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>

        <?php else : ?>
          <p class="hs-booking-not-found">
            No tienes hand-shakes en este día.
            Programa uno y no te quedes por fuera. ;)
          </p>
        <?php endif;
        ?>
      </div>
    </div>
<?php
    return ob_get_clean();
  }
}


// Función de comparación para ordenar los bloques de horas por hora de inicio
function comparar_horas($a, $b)
{
  return strtotime($a['bloque_horas']['hora_inicio']) - strtotime($b['bloque_horas']['hora_inicio']);
}


// Función para eliminar cita agendada con un empresario
function eliminar_cita()
{
  if (isset($_POST['reserva'], $_POST['reservas_realizadas'])) {
    $reservacion = json_decode(stripslashes($_POST['reserva']), true);
    $reservadas_deserializadas = json_decode(stripslashes($_POST['reservas_realizadas']), true);
    $current_user = get_current_user_id();

    // Extraer el empresario_id
    $empresario_id = $reservacion['empresario_id'];

    // Filtrar los elementos del primer array que no coincidan con el empresario_id
    $reservadas_filtradas = array_filter($reservadas_deserializadas, function ($item) use ($empresario_id) {
      return $item['empresario_id'] !== $empresario_id;
    });

    // Ahora $reservadas_filtradas contiene los elementos del primer array que no coinciden con el empresario_id
    $reservadas_filtradas_serializadas = serialize($reservadas_filtradas);
    update_user_meta($current_user, 'reservaciones_realizadas', $reservadas_filtradas_serializadas);
    wp_die(json_encode(array('message' => 'Cita eliminada con éxito', 'type' => 'success')));
  }
}
add_action('wp_ajax_eliminar_cita', 'eliminar_cita');
add_action('wp_ajax_nopriv_eliminar_cita', 'eliminar_cita');
