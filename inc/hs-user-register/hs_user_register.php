<?php
// Registrar Nuevos Usuarios

function registrar_nuevo_usuario($cf7)
{

  if ($cf7->id() == '325') {
    $submission = WPCF7_Submission::get_instance();

    if ($submission) {
      $posted_data = $submission->get_posted_data();

      $fullName = $posted_data['nombre-apellido'];
      $email = $posted_data['correo-electronico'];
      $phoneNumber = $posted_data['telefono'];
      $empresa = $posted_data['nombre-empresa'];
      $empresaDescription = $posted_data['desc-empresa'];
      $websiteURL = $posted_data['pagina-web'];
      $redSocial1 = $posted_data['rs-1'];
      $redSocial2 = $posted_data['rs-2'];
      $redSocial3 = $posted_data['rs-3'];
      $address = $posted_data['direccion-empresa'];
      $location = $posted_data['ciudad-empresa'];
      $modelo = $posted_data['modelo-negocio'];
      $exprerience = $posted_data['experiencia'];
      $diferencial = $posted_data['diferencial'];
      $exprerience = $posted_data['experiencia'];

      $fullNameArray = explode(' ', $fullName);
      $firstName = $fullNameArray[0];
      $lastName = $fullNameArray[1];

      $user = get_user_by('email', $email);
      if ($user) $user_id = $user->ID;

      if (!is_wp_error($user_id)) {
        // El usuario ha sido creado correctamente

        // Ahora guardamos el nombre y apellido en los metadatos del usuario
        update_user_meta($user_id, 'first_name', $firstName);
        update_user_meta($user_id, 'last_name', $lastName);
        update_user_meta($user_id, 'telefono', $phoneNumber);
        update_user_meta($user_id, 'empresa', $empresa);
        update_user_meta($user_id, 'que_hace_la_empresa', $empresaDescription);
        update_user_meta($user_id, 'url_website', $websiteURL);
        update_user_meta($user_id, 'red_social_1', $redSocial1);
        update_user_meta($user_id, 'red_social_2', $redSocial2);
        update_user_meta($user_id, 'red_social_3', $redSocial3);
        update_user_meta($user_id, 'red_social_3', $redSocial3);
        update_user_meta($user_id, 'direccion', $address);
        update_user_meta($user_id, 'ciudad', $location);
        update_user_meta($user_id, 'modelo_de_negocio', $modelo);
        update_user_meta($user_id, 'experiencia_en_el_mercado', $exprerience);
        update_user_meta($user_id, 'por_que_es_diferente', $diferencial);
      } else {
        // Ha ocurrido un error al crear el usuario
      }
    }
  }
}
add_action('wpcf7_before_send_mail', 'registrar_nuevo_usuario');
