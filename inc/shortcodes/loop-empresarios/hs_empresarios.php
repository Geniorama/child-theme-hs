<?php

/**
 * Query para listar a todos los empresarios, 
 * controlando el botón de disponible o agenda llena.
 */
add_shortcode('hs_empresarios', 'hs_listar_empresarios');

function hs_listar_empresarios()
{
  // Carga los estilos CSS
  wp_enqueue_style('table-hs', get_stylesheet_directory_uri() . '/inc/shortcodes/loop-empresarios/hs_style_empresario.css');

  $args = array(
    'post_type'     => 'empresarios',
    'posts_per_page'  => -1,
  );

  $query = new WP_Query($args);
  $html = '';
  $html .= "<div class='hs-grid-empresarios'>";

  if ($query->have_posts()) :
    while ($query->have_posts()) :
      $query->the_post();
      $horarios_rueda_de_negocios = get_field('horarios_rueda_de_negocios');

      if ($horarios_rueda_de_negocios) :
        $disponibilidad_empresario = hs_verificar_empresario_disponible($horarios_rueda_de_negocios);
        $html .= hs_template_empresario($disponibilidad_empresario);
      endif;
    endwhile;
    wp_reset_postdata(); ?>

  <?php else : ?>
    <p><?php esc_html_e('Aún no hay empresarios agregados'); ?></p>
<?php endif;
  $html .= "</div>";
  return $html;
}

function hs_verificar_empresario_disponible($horarios_rueda_de_negocios)
{
  foreach ($horarios_rueda_de_negocios as $horario) :
    if (!$horario['agendar']) return true;
  endforeach;
  return false;
}

function hs_template_empresario($disponibilidad_empresario)
{
  $img = get_field('foto_perfil');
  $perfil_empresario = $img['sizes']['medium_large'];
  $nombre_empresario = $img['sizes']['medium_large'];
  $empresa = get_field('nombre_empresa');
  $cargo = get_field('cargo');
  $html_empresa = "
    <div class='box-empresa'>
      <h3 class='empresario-descripcion'>$empresa</h3>
    </div>";
  $html_cargo = "
    <div class='box-cargo'>
      <h3 class='empresario-descripcion'>$cargo</h3>		
    </div>";

  $template = $disponibilidad_empresario ?
    do_shortcode('[INSERT_ELEMENTOR id="1472"]') :
    do_shortcode('[INSERT_ELEMENTOR id="1475"]');

  $html = '';
  $html .= '<div class="empresario">';
  $html .= "<div class='container-image'><img src=$perfil_empresario alt=$nombre_empresario></div>";
  $html .= $template;
  $html .= $html_empresa;
  $html .= $html_cargo;
  $html .= '</div>';
  return $html;
}
?>