var livestampEnabled = localizedData.livestamp_timeout > 0;
if (livestampEnabled) {
    jQuery.livestamp.interval(localizedData.livestamp_timeout * 1000);
}

jQuery(document).ready(function($) {
    $('.disqus-rcw-list').each(function() {
        getLastComments($(this));
    });

    $('.disqus-rcw-update').click(function(e) {
        getLastComments($(this).siblings('.disqus-rcw-list'));
    });

    function getLastComments($widget) {
        var data = {
            'action': 'load_recent_comments',
            'number': $widget[0].dataset.widgetNumber
        }
        var $updateButton = $widget.siblings('.disqus-rcw-update');
        $updateButton.prop('disabled', true);
        $.post(localizedData.ajax_url, data, function(response) {
            var $response = livestampEnabled ? updateLivestamp($(response)) : $(response);
            $widget.html($response);
            $updateButton.prop('disabled', false);
        });
    }

    function updateLivestamp($html) {
        $('[data-livestamp]', $html).each(function() {
            $(this).livestamp(parseInt(this.dataset.livestamp));
        });
        return $html;
    }
});
