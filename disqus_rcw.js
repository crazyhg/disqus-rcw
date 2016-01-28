function getLastComments(widgetNumber, parent) {
    var data = {
        'action': 'load_recent_comments',
        'number': widgetNumber
    };
    jQuery.post(localizedData.ajax_url, data, function(response) {
        parent.html(response);
    });
}
jQuery(document).ready(function($) {
    $( ".disqus-rcw-list" ).each(function( index ) {
        getLastComments(this.dataset.widgetNumber, $(this));
    });
});