/**
 * Some of the code written, maintained by Darko Gjorgjijoski
 */

document.addEventListener('DOMContentLoaded', function(event) {
    let selectGenerator = jQuery('select#generator');
    

    const productDropdownSearchConfig = {
        ajax: {
            cache: true,
            delay: 500,
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: function(params) {
                return {
                    action: 'lmfwc_dropdown_search',
                    security: security.dropdownSearch,
                    term: params.term,
                    page: params.page,
                    type: 'generator'
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            }
        },
        placeholder: 'Search by generator',
        minimumInputLength: 1,
        allowClear: true
  };

    if (selectGenerator) {
        selectGenerator.select2(productDropdownSearchConfig);
    }

  
});

var setProgressBar = function(id, message, percent) {
        var progressBarValue = jQuery('#' + id + ' .lmfwc-tool-progress-bar-inner');
        var progressBarInfo = jQuery('#' + id + ' .lmfwc-tool-progress-info');
        var progressBarRow = jQuery('#' + id + ' .lmfwc-tool-form-row-progress');
        progressBarValue.css('width', percent + '%');
        progressBarInfo.html(message + ' ' + '(' + percent + '%)');
        progressBarRow.show();
};

var processTool = function ( form, page ) { 

        var formId = form.attr('id');
        var data = form.serializeArray();
        var url = security.ajaxurl + '?action=lmfwc_handle_tool_process&_wpnonce=' + security.dropdownSearch;
        var submitButton = jQuery('button[type=submit]');
        //submitButton.addClass('disabled');
        window.onbeforeunload = function () {
            return true;
        }
        var pageData = {name:'page', value: page};
        data.push(pageData);
        jQuery.ajax({
            url: url,
            data: data,
            type: 'POST',
            success: function (response, responseStatus, responseHeaders) {
                console.log('success');
                var next_page = response.data.next_page;
                var message = response.data.message;
                var percent = response.data.percent;
                setProgressBar(formId, message, percent);
                if ( next_page >= 0) {
                    setTimeout(function () {
                        processTool(form,next_page)
                    }, 2000);
                } else {
                    // Remove navigation prompt
                    window.onbeforeunload = null;
                    submitButton.removeClass('disabled');
                    submitButton.hide()
                }
            },
            error: function (response, responseStatus, responseHeaders) {
                alert('HTTP Error');
                // Remove navigation prompt
                window.onbeforeunload = null;
            }
        });
    }


document.addEventListener('DOMContentLoaded', function(event) {
	const selectUser = jQuery('select#user');
	if (selectUser) selectUser.select2();

    jQuery('#lmfwc-generate-tool').submit(function (e){
        e.preventDefault();
        processTool(jQuery(this), 1);
    });
    jQuery('#lmfwc-migrate-tool').submit(function (e){
        e.preventDefault();
        processTool(jQuery(this), 1);
        
    });
    
});


(function ($) {

    'use strict';
    // The "Upload" button
    $(document).on('click', '.lmfwc-field-upload-button', function () {
        window.wpActiveEditor = null;
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var show_attachment_preview = $(this).closest('.lmfwc-field-upload').data('show-attachment-preview')
        var button = $(this);
        wp.media.editor.send.attachment = function (props, attachment) {
            if (show_attachment_preview) {
                $(button).parent().prev().attr('src', attachment.url);
            }
            $(button).prev().val(attachment.id);
            wp.media.editor.send.attachment = send_attachment_bkp;
        };
        wp.media.editor.open(null, {
            frame: 'post',
            state: 'insert',
            multiple: false
        });
        return false;
    });

    // The "Remove" button (remove the value from input type='hidden')
    $(document).on('click', '.lmfwc-field-remove-button', function () {
        var answer = confirm('Are you sure?');
        if (answer) {
            var show_attachment_preview = $(this).closest('.lmfwc-field-upload').data('show-attachment-preview')
            if (show_attachment_preview) {
                var src = $(this).parent().prev().attr('data-src');
                $(this).parent().prev().attr('src', src);
            }
            $(this).prev().prev().val('');
        }
        return false;
    });
})(jQuery);
