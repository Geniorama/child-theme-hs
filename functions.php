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


function enqueue_slick_assets()
{
	// Estilos de Slick Carousel
	wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', array(), '1.8.1');
	wp_enqueue_style('slick-theme-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css', array(), '1.8.1');

	// Scripts de Slick Carousel
	wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
}
add_action('wp_enqueue_scripts', 'enqueue_slick_assets');

function enqueue_custom_script()
{
	wp_enqueue_script('agendarcita-script', get_stylesheet_directory_uri() . '/src/js/agendarcita.js', array('jquery'), null, true);

	// Pasar la URL de admin-ajax.php al script
	wp_localize_script('agendarcita-script', 'agendarcita', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'enqueue_custom_script');


/**
 * Shortcode para agrear en el single [mostrar_usuario]
 */
function mostrar_usuario_shortcode()
{
	global $post;
	$usuario = get_field('id_usuario');
	$post = get_post($usuario);
	setup_postdata($post);
	$nombre = get_field('nombre');
	$apellido = get_field('apellido');
	$nombre_empresa = get_field('nombre_empresa');
	$horarios_rueda_de_negocios = get_field('horarios_rueda_de_negocios');
	$html = '';
	$html .=  '<h1 style="color: #000;">' . $nombre . ' ' . $apellido . '</h1>';
	$html .= '<h1 style="color: #000;">' . $nombre_empresa . '</h1>';

	if ($horarios_rueda_de_negocios) :
		foreach ($horarios_rueda_de_negocios as $indice => $horario) :
			$html .= '<div class="" style="display: flex; justify-content: space-between; background: #000; color: #fff; font-size: 18px; padding: 10px;    margin-bottom: 10px;">';
			$html .= '<div>';
			$html .= '<p>' . $horario['hora_inicio'] . '</p>';
			$html .= '<p>' . $horario['hora_fin'] . '</p>';
			$html .= '</div>';
			$html .= '<div>';
			if ($horario['agendar'] == true) :
				$html .= 'NO DISPONIBLE';
			else :
				// Agregar un identificador único al botón
				$html .= '<button class="agendar-button" data-bloque-hora-id="' . esc_attr($indice) . '" data-id-usuario="' . esc_attr($usuario) . '">AGENDAR HAND-SHAKE</button>';
			endif;
			$html .= '</div>';
			$html .= '</div>';
		endforeach;
	endif;
	return $html;
}

add_shortcode('mostrar_usuario', 'mostrar_usuario_shortcode');


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
			$reservaciones_realizadas_empresarios_actualizado = get_user_meta(get_current_user_id(), 'reservaciones_realizadas_empresarios', true);

			// Deserializar para obtener el array
			$reservaciones_realizadas_empresarios_actualizado = unserialize($reservaciones_realizadas_empresarios_actualizado);

			// Aquí se comprueba que el usuario no haya realizado una reserva anteriormente con el mismo empresario
			if (!$reservaciones_realizadas_empresarios_actualizado || !in_array($usuario_id, $reservaciones_realizadas_empresarios_actualizado)) {
				// Actualizar el campo 'agendar' a true para el bloque de hora especificado
				$horarios_rueda_de_negocios[$bloque_hora_id]['agendar'] = true;

				// Guardar los cambios en la base de datos
				update_field('horarios_rueda_de_negocios', $horarios_rueda_de_negocios, $usuario_id);

				// Guardar el bloque de hora reservado en el perfil del usuario
				$bloque_hora_reservado = $horarios_rueda_de_negocios[$bloque_hora_id];

				// Obtener el valor actualizado de bloques_horas_reservados
				$bloques_horas_reservados_actualizado = get_user_meta(get_current_user_id(), 'bloques_horas_reservados', true);

				// Deserializar para obtener el array
				$bloques_horas_reservados_actualizado = unserialize($bloques_horas_reservados_actualizado);

				// Agregar el nuevo valor si no existe
				if (!$bloques_horas_reservados_actualizado) {
					$bloques_horas_reservados_actualizado = array();
				}

				// Agregar el nuevo bloque de hora reservado al array
				$bloques_horas_reservados_actualizado[] = $bloque_hora_reservado;

				// Serializar antes de actualizar el campo
				$bloques_horas_reservados_serializados = serialize($bloques_horas_reservados_actualizado);

				// Actualizar el campo bloques_horas_reservados con los nuevos valores serializados
				update_user_meta(get_current_user_id(), 'bloques_horas_reservados', $bloques_horas_reservados_serializados);

				// Actualizar el campo reservaciones_realizadas_empresarios con los nuevos valores
				$reservaciones_realizadas_empresarios_actualizado[] = $usuario_id;

				// Serializar antes de actualizar el campo
				$reservaciones_serializadas = serialize($reservaciones_realizadas_empresarios_actualizado);

				// Actualizar el campo reservaciones_realizadas_empresarios con los nuevos valores serializados
				update_user_meta(get_current_user_id(), 'reservaciones_realizadas_empresarios', $reservaciones_serializadas);
			}

			echo 'Éxito'; // Puedes enviar cualquier respuesta que desees de vuelta al frontend
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



/**
 * Shorcode [mostrar_informacion_reserva_usuario]
 * para mostrar lista de reservas
 * Agregar este shortcode en la página de agendamiento
 */
function mostrar_informacion_reserva_usuario_shortcode()
{
	// Obtener el valor serializado de la base de datos
	$reservaciones_serializadas = get_user_meta(get_current_user_id(), 'reservaciones_realizadas_empresarios', true);
	$bloque_horas_reservadas_serializadas = get_user_meta(get_current_user_id(), 'bloques_horas_reservados', true);

	// Deserializar para obtener el array
	$reservaciones_deserializadas = unserialize($reservaciones_serializadas);

	$bloque_horas_reservadas_deserializadas = unserialize($bloque_horas_reservadas_serializadas);

	// Verificar si hay reservaciones
	if ($reservaciones_deserializadas && is_array($reservaciones_deserializadas)) {
		$html = '';
		$html .= '<table>';
		$html .= '<tr><th>ID Empresario</th><th>Nombre Empresario</th><th>Rango de Horas Reservadas</th></tr>';

		foreach ($reservaciones_deserializadas as $indice => $empresario_id) {
			$empresario = get_post($empresario_id);
			$nombre_empresario = ($empresario) ? get_the_title($empresario) : 'Empresario no encontrado';

			$rango_horas_reservadas = '';
			if (isset($bloque_horas_reservadas_deserializadas[$indice]) && is_array($bloque_horas_reservadas_deserializadas[$indice])) {
				$rango_horas_reservadas = $bloque_horas_reservadas_deserializadas[$indice]['hora_inicio'] . ' - ' . $bloque_horas_reservadas_deserializadas[$indice]['hora_fin'];
			}

			$html .= '<tr>';
			$html .= '<td>' . esc_html($empresario_id) . '</td>';
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
