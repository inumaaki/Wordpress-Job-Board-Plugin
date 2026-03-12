jQuery(document).ready(function ($) {
    $('#bjb-application-form').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var messageBox = form.find('.bjb-message');
        var submitBtn = form.find('button[type="submit"]');

        submitBtn.prop('disabled', true).text('Sending...');
        messageBox.hide().removeClass('error success');

        var formData = new FormData(this);
        formData.append('action', 'bjb_submit_application');

        $.ajax({
            url: bjb_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    messageBox.text(response.data.message).addClass('success').show();
                    form[0].reset();
                    setTimeout(function () {
                        $('.bjb-modal').fadeOut();
                    }, 2000);
                } else {
                    messageBox.text(response.data.message).addClass('error').show();
                }
            },
            error: function () {
                messageBox.text('An error occurred. Please try again.').addClass('error').show();
            },
            complete: function () {
                submitBtn.prop('disabled', false).text('Submit Application');
            }
        });
    });

    // Modal Logic
    $('.bjb-open-modal').on('click', function (e) {
        e.preventDefault();
        $('.bjb-modal').fadeIn().css('display', 'flex');
    });

    $('.bjb-modal-close').on('click', function () {
        $('.bjb-modal').fadeOut();
    });

    $(window).on('click', function (e) {
        if ($(e.target).hasClass('bjb-modal')) {
            $('.bjb-modal').fadeOut();
        }
    });

    /* --- Job Filtering Logic --- */
    /* --- Job Filtering Logic --- */
    var currentPage = 1;
    var maxPages = 1; // Default

    // Add "View More" button dynamically if not there
    if ($('#bjb-load-more-btn').length === 0) {
        $('#bjb-results-container').after('<div class="bjb-load-more-container" style="text-align:center; margin-top:20px; display:none;"><button id="bjb-load-more-btn" class="button bjb-btn">View More Jobs</button></div>');
    }

    function bjb_filter_jobs(page) {
        if (typeof page === 'undefined') {
            page = 1;
        }
        currentPage = page;

        var jobType = $('#bjb-filter-type').val();
        var category = $('#bjb-filter-category').val();
        var remote = $('#bjb-filter-remote').is(':checked') ? 1 : 0;
        var keywords = $('#bjb-search-keywords').val();
        // Check Header Location OR Sidebar Location
        var location = $('#bjb-search-location').val();
        if (!location) {
            location = $('#bjb-filter-location-sidebar').val();
        }

        var container = $('#bjb-results-container');
        var loadMoreBtn = $('#bjb-load-more-btn');
        var loadMoreContainer = $('.bjb-load-more-container');

        // Loader State
        if (page === 1) {
            container.css('opacity', '0.5');
        } else {
            loadMoreBtn.text('Loading...').prop('disabled', true);
        }

        $.ajax({
            url: bjb_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'bjb_filter_jobs',
                job_type: jobType,
                job_category: category,
                remote: remote,
                keywords: keywords,
                location: location,
                paged: page
            },
            success: function (response) {
                if (response.success) {
                    if (page === 1) {
                        container.html(response.data.html);
                    } else {
                        container.append(response.data.html);
                    }

                    // Update Max Pages
                    maxPages = response.data.max_pages;

                    // Show/Hide Load More
                    if (currentPage < maxPages) {
                        loadMoreContainer.show();
                        loadMoreBtn.text('View More Jobs').prop('disabled', false);
                    } else {
                        loadMoreContainer.hide();
                    }

                } else {
                    if (page === 1) container.html('<p>Error loading jobs.</p>');
                }
                container.css('opacity', '1');
            },
            error: function () {
                if (page === 1) container.html('<p>Connection error.</p>');
                container.css('opacity', '1');
                loadMoreBtn.text('View More Jobs').prop('disabled', false);
            }
        });
    }

    // Sidebar Filters (Change) -> Reset to Page 1
    $('#bjb-filter-type, #bjb-filter-category, #bjb-filter-remote, #bjb-filter-location-sidebar').on('change', function () {
        bjb_filter_jobs(1);
    });

    // Search Form (Submit) -> Reset to Page 1
    $('#bjb-search-form').on('submit', function (e) {
        e.preventDefault();
        bjb_filter_jobs(1);
    });

    // Search Inputs (Debounced Keyup) -> Reset to Page 1
    var timeout = null;
    $('#bjb-search-keywords, #bjb-search-location').on('keyup', function () {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            bjb_filter_jobs(1);
        }, 600);
    });

    // Load More Click
    $(document).on('click', '#bjb-load-more-btn', function (e) {
        e.preventDefault();
        bjb_filter_jobs(currentPage + 1);
    });

});
