<?php

/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0');

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles()
{

	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		HELLO_ELEMENTOR_CHILD_VERSION
	);
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20);

require "inc/hs-booking/sc_booking.php";
require "inc/helpers/sc_show_name.php";
require "inc/helpers/sc_show_photo.php";

/**
 * SHORTCODE PARA MOSTRAR AGENDA PARA RESERVAR
 * [hs_booking]
 */


function enqueue_slick_assets()
{
	// Estilos de Slick Carousel
	wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', array(), '1.8.1');
	wp_enqueue_style('slick-theme-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css', array(), '1.8.1');

	// Scripts de Slick Carousel
	wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
}
add_action('wp_enqueue_scripts', 'enqueue_slick_assets');



/**
 * Shorcode [mostrar_informacion_reserva_usuario]
 * para mostrar lista de reservas
 * Agregar este shortcode en la página de agendamiento
 */
function mostrar_informacion_reserva_usuario_shortcode()
{
	$reservadas_serializadas = get_user_meta(get_current_user_id(), 'reservaciones_realizadas', true);

	$reservadas_deserializadas = unserialize($reservadas_serializadas);

	// Verificar si hay reservaciones
	if ($reservadas_deserializadas && is_array($reservadas_deserializadas)) {
		$html = '<table>';
		$html .= '<tr><th>Nombre Empresario</th><th>Rango de Horas Reservadas</th></tr>';

		foreach ($reservadas_deserializadas as $reservacion) {
			$empresario_id = $reservacion['empresario_id'];
			$bloque_horas = $reservacion['bloque_horas'];

			$empresario = get_post($empresario_id);
			$nombre_empresario = ($empresario) ? get_the_title($empresario) : 'Empresario no encontrado';

			$rango_horas_reservadas = '';
			if (is_array($bloque_horas) && isset($bloque_horas['hora_inicio'], $bloque_horas['hora_fin'])) {
				$rango_horas_reservadas = $bloque_horas['hora_inicio'] . ' - ' . $bloque_horas['hora_fin'];
			}

			$html .= '<tr>';
			$html .= '<td>' . esc_html($nombre_empresario) . '</td>';
			$html .= '<td>' . esc_html($rango_horas_reservadas) . '</td>';
			$html .= '</tr>';
		}

		$html .= '</table>';
	} else {
		$html = 'No hay reservaciones realizadas.';
	}

	return $html;
}

add_shortcode('mostrar_informacion_reserva_usuario', 'mostrar_informacion_reserva_usuario_shortcode');
