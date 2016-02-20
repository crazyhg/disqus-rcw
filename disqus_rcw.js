var livestampEnabled = localizedData.livestamp_timeout > 0;
if (livestampEnabled) {
    jQuery.livestamp.interval(localizedData.livestamp_timeout * 1000);
}

jQuery(document).ready(function($) {
    $('.disqus-rcw-list').each(function() {
        getLastComments($(this));
    });

    function getLastComments($widget) {
        var data = {
            'action': 'load_recent_comments',
            'number': $widget[0].dataset.widgetNumber
        }
        $.post(localizedData.ajax_url, data, function(response) {
            var $response = livestampEnabled ? updateLivestamp($(response)) : $(response);
            $widget.html($response);
        });
    }

    function updateLivestamp($html) {
        $('[data-livestamp]', $html).each(function() {
            $(this).livestamp(parseInt(this.dataset.livestamp));
        });
        return $html;
    }
});
