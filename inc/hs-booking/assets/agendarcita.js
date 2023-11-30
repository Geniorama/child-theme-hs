// Paso 2: Manejador de eventos en JavaScript
document.addEventListener("DOMContentLoaded", function () {
  var buttons = document.querySelectorAll(".agendar-button");
  buttons.forEach(function (button) {
    button.addEventListener("click", function () {
      var bloqueHoraId = button.dataset.bloqueHoraId;
      var idUsuario = button.dataset.idUsuario; // Agregar esta línea

      // Paso 3: Configuración de AJAX
      var xhr = new XMLHttpRequest();
      xhr.open("POST", agendarcita.ajaxurl, true);
      xhr.setRequestHeader(
        "Content-Type",
        "application/x-www-form-urlencoded; charset=UTF-8"
      );
      xhr.onload = function () {
        if (xhr.status === 200) {
          // Manejar la respuesta del servidor
          if (xhr.responseText === "Éxito") {
            // Recargar la página después de una reserva exitosa
            location.reload();
            console.log('registro exitoso')
          } else {
            // Puedes agregar lógica adicional aquí para manejar otros casos de respuesta
            console.log("Error al reservar el bloque de hora");
          }
        }
      };
      xhr.send(
        "action=reservar_bloque_hora&bloque_hora_id=" +
          bloqueHoraId +
          "&id_usuario=" +
          idUsuario
      );
    });
  });
});
