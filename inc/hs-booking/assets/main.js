jQuery(function ($) {
    // Estado inicial de la modal
    $(".hs-modal-booking").hide();

    // Cierra el modal en cualquier clic en la web
	$("html").click(function(){
        $(".hs-modal-booking").hide();
    });

    $('.hs-btn-cancel').click(function(){
        $(".hs-modal-booking").hide();
    });

   //Evita que el modal se cierre al hacer clic en la card
   $(".hs-modal-booking__card").click(function(e){
        e.stopPropagation();
    });


    //Abre el modal con los botones de la lista
    $(".hs-body__list .agendar-button").click(function(e){
        e.stopPropagation()
        $(".hs-modal-booking").show().css('opacity', '1');
        var bloqueHoraId = $(this).attr('data-bloque-hora-id');
        var idUsuario = $(this).attr('data-id-usuario');
        
        $('.hs-btn-continue').off('click').click(function(e){
            e.stopPropagation()
            $(this).prop("disabled", true); // Deshabilita el botón para evitar que presionen clic varias veces seguidas
            $.ajax({
                url: agendarcita.ajaxurl,
                type: 'post',
                data: {
                    action: 'reservar_bloque_hora',
                    bloque_hora_id: bloqueHoraId,
                    id_usuario: idUsuario
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.type === 'success') {
                        window.location.href = 'https://handshakers.copu.media/confirmacion-cita/?id-empresario=' + idUsuario;
                    } else if (data.type === 'error') {
                        alert(data.message);
                        location.reload();
                    }
                }
            });
        })
    })

    
    $(".hs-eliminar-reserva").click(function(e) {
        e.stopPropagation();
        const reserva = $(this).attr('data-reserva');
        const reservasRealizadas = $(this).attr('data-reservas-realizadas');
        const currentUser = $(this).data('user');
        $.ajax({
            url: eliminarcita.ajaxurl,
            type: 'post',
            data: {
                action: 'eliminar_cita',
                reserva: reserva,
                reservas_realizadas: reservasRealizadas,
                current_user: currentUser
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.type === 'success') {
                    alert(data.message);
                    location.reload();
                } else if (data.type === 'error') {
                    alert(data.message);
                    location.reload();
                }
            }
        });
    })
    
});