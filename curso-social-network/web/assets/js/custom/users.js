$(document).ready(function () {

    var ias = jQuery.ias({
        container: '.box-users',
        item: '.user-item',
        pagination: '.pagination',
        next: '.pagination .next_link',
        triggerPageThreshold: 5
    });

    ias.extension(new IASTriggerExtension(
            {
                text: 'Ver mas personas',
                offset: 3

            }));


    ias.extension(new IASSpinnerExtension(
            {
                src: URL + '/..//assets/images/ajax-loader.gif'

            }));

    ias.extension(new IASNoneLeftExtension(
            {
                text: 'No hay mas personas'

            }));


});