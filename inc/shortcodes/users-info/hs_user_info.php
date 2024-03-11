<?php

/**
 * Shotcode para traer información de los usuarios
 * para usarlo en el template de single user
 * example shortcode [user_info data='nombre']
 */
add_shortcode('user_info', 'hs_user_info');

function hs_user_info($atts)
{
  $user_id = get_the_author_meta('ID');
  switch ($atts['data']) {
    case 'nombre':
      return get_the_author_meta('first_name', $user_id);
    case 'apellido':
      return get_the_author_meta('last_name', $user_id);
  }
}
