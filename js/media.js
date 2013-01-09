// props: http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/
jQuery(document).ready(function($) {
    var frames = {};

    if (typeof wp.media.frames.pmgcore == 'undefined') {
        wp.media.frames.pmgcore = {};
    }

    $('#wpbody').on('click', '.pmgcore-cue-media', function(e) {

        e.preventDefault();

        var parent = $(this).parents('.pmgcore-media-wrap'),
            target = $(parent).find('input[type="hidden"]'),
            target_id = $(target).attr('id');
            media_id = $(target).val();

        if (frames[target_id]) {
            frames[target_id].open();
            return;
        }

        frames[target_id] = wp.media.frames.pmgcore[target_id] = wp.media({
            title:  $(this).data('title'),
            button: {
                text: $(this).data('title')
            },
            multiple: false,
            selection: [media_id]
        });

        frames[target_id].on('select', function() {
            var att = frames[target_id].state().get('selection').first(),
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

            frames[target_id].close();
        });

        frames[target_id].open();
    }).on('click', '.pmgcore-remove-media', function(e) {
        var $p = $(this).parents('.pmgcore-media-wrap');

        e.preventDefault();

        $p.find('input[type="hidden"]').val(0);
        $p.find('.pmgcore-attachment-container').html('');
    });
});
