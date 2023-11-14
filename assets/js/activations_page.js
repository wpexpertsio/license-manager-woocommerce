document.addEventListener('DOMContentLoaded', function() {
    const dropdownLicenses   = jQuery('select#filter-by-license-id');
    const dropdownSources = jQuery('select#filter-by-source');

    const licenseDropdownSearchConfig = {
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
                    type: 'license'
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
        placeholder: i18n.placeholderSearchLicenses,
        minimumInputLength: 1,
        allowClear: true
    };
    const sourceDropdownSearchConfig = {
        placeholder: i18n.placeholderSearchSources,
        allowClear: true
    };
    if (dropdownLicenses) {
        dropdownLicenses.select2(licenseDropdownSearchConfig);
    }

    if (dropdownSources) {
        dropdownSources.select2(sourceDropdownSearchConfig);
    }

});
