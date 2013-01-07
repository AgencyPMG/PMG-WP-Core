// props: http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/
jQuery(document).ready(function($) {
    var file_frame;

    $('#wpbody').on('click', '.pmgcore-cue-media', function(e) {

        e.preventDefault();

        var parent = $(this).parents('.pmgcore-media-wrap'),
            target = $(parent).find('input[type="hidden"]'),
            media_id = $(target).val();

        if (file_frame) {
            file_frame.open();
            return;
        }

        file_frame = wp.media.frames.file_frame = wp.media({
            title:  $(this).data('title'),
            button: {
                text: $(this).data('title')
            },
            multiple: false,
            selection: [media_id]
        });

        file_frame.on('select', function() {
            var att = file_frame.state().get('selection').first(),
                type = att.get('type'),
                sizes,
                size,
                e;

            $(target).val(att.get('id'));

            if ('image' == type) {
                sizes = att.get('sizes');

                size = sizes.thumbnail || sizes.medium;
                e = $('<img />', {src: size.url, width: '150'});
            } else {
                e = $('<input />', {
                    type: 'text',
                    disabled: 'disabled',
                    class: 'widefat',
                    value: att.get('url')
                });
            }

            $(parent).find('.pmgcore-attachment-container').html('').append(e);

            file_frame.close();
        });

        file_frame.open();
    }).on('click', '.pmgcore-remove-media', function(e) {
        var $p = $(this).parents('.pmgcore-media-wrap');

        e.preventDefault();

        $p.find('input[type="hidden"]').val(0);
        $p.find('.pmgcore-attachment-container').html('');
    });
});
