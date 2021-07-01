var varnishLinkActionUrl = '/varnish/link'
var varnishWebsiteLinkCheckbox = jQuery('.varnish-website-link-checkbox');

if (varnishWebsiteLinkCheckbox.length) {
    varnishWebsiteLinkCheckbox.on('click', function(e){
        var checkbox = jQuery(this);
        
        var postData = {
            'varnishId' : checkbox.attr('data-varnish-id'),
            'websiteId' : checkbox.attr('data-website-id'),
            'isChecked' : checkbox.is(':checked')
        };

        var ajaxCall = jQuery.ajax({
            url: varnishLinkActionUrl,
            type: "POST",
            data: postData
        });

        ajaxCall.done(function(response){
            response = JSON.parse(response);
            if (response.status == 'error') {
                console.log("Error: " + response.message);
            } else {
                console.log("Success: " + response.message);
            }
        });
    });
} else {
    console.log("Varnish website checkbox not found");
}