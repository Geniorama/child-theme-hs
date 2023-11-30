// document.addEventListener("DOMContentLoaded", function () {
//   var buttons = document.querySelectorAll(".agendar-button");
//   buttons.forEach(function (button) {
//     button.addEventListener("click", function () {
//       var bloqueHoraId = button.dataset.bloqueHoraId;
//       var idUsuario = button.dataset.idUsuario;



//       // Configuración de AJAX
//       var xhr = new XMLHttpRequest();
//       xhr.open("POST", agendarcita.ajaxurl, true);
//       xhr.setRequestHeader(
//         "Content-Type",
//         "application/x-www-form-urlencoded; charset=UTF-8"
//       );
//       xhr.onload = function () {
//         if (xhr.status === 200) {
//           // Manejar la respuesta del servidor
//           if (xhr.responseText === "Éxito") {
//             // Recargar la página después de una reserva exitosa
//             location.reload();
//           } else {
//             // Mostrar un mensaje de error al usuario
//             alert(xhr.responseText);
//           }
//         }
//       };
//       xhr.send(
//         "action=reservar_bloque_hora&bloque_hora_id=" +
//           bloqueHoraId +
//           "&id_usuario=" +
//           idUsuario
//       );
//     });
//   });
// });
