jQuery(document).ready(function($) {

    // Color Picker
    $('.hd-color-picker').wpColorPicker();

    // Media Upload
    $('body').on('click', '.hd-upload-button', function(e) {

        e.preventDefault();

        var upload_input = $(this).siblings('.hd-upload-input'),
            hd_uploader;

        if (hd_uploader) {
            hd_uploader.open();
            return;
        }
        hd_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Upload Media',
            button: {
                text: 'Select',
            },
            multiple: false
        });

        hd_uploader.on('select', function() {
            var media_file = hd_uploader.state().get('selection').first().toJSON();
            upload_input.val(media_file.url);
        });

        hd_uploader.open();

    });

});