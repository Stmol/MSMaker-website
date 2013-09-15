'use strict';

$(document).ready(function () {
    $(".fancybox").fancybox({
        padding: 2,
        helpers: {
            overlay: {
                css: {
                    'background': 'rgba(0, 0, 0, 0.2)'
                }
            }
        }
    });
});