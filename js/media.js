jQuery(document).ready(function($) {
    var Attachment = wp.media.model.Attachment;

    $('#wpbody').on('click', '.pmgcore-cue-media', function(e) {
        var mediaID = false, $t = $(this), attachment, options, $target, $p, frame;

        e.preventDefault();

        $p = $t.parents('.pmgcore-media-wrap');
        // why can't I just select by ID here?
        $target = $p.find('input[type="hidden"]');

        mediaID = $target.val();

        if(!mediaID || -1 == mediaID || '0' == mediaID) {
            mediaID = false;
        }

        attachment = Attachment.get(mediaID);
        attachment.fetch();

        options = {
            title: $t.data('title'),
            multiple: false
        };

        if(mediaID) {
            options.selection = [attachment];
        }

        frame = wp.media(options);

        frame.toolbar.on('activate:select', function() {
            frame.toolbar.view().set({
                select: {
                    style: 'primary',
                    text: $t.data('update'),
                    click: function() {
                        var m = frame.state().get('selection').first();
                            type = m.get('type'),
                            e;

                        frame.close();

                        $target.val(m.get('id'));



                        if('image' == type) { 
                            var sizes = m.get('sizes'), size;

                            if(sizes) {
                                size = sizes['post-thumbnail'] || sizes.medium;
                            }

                            size = size || m.toJSON();

                            e = $('<img />', {src: size.url, width: '150'});
                        } else {
                            e = $('<input />', {
                                type: 'text', disabled: 'disabled',
                                class: 'widefat', value: m.get('url')});
                        }

                        $p.find('.pmgcore-attachment-container')
                          .html('')
                          .append(e);
                    }
                }
            });
        });
    }).on('click', '.pmgcore-remove-media', function(e) {
        var $t = $(this), $p = $t.parents('.pmgcore-media-wrap');

        e.preventDefault();

        $p.find('input[type="hidden"]').val(0);
        $p.find('.pmgcore-attachment-container').html('');
    });
});
