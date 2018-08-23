<!-- Cookie nag -->
<link rel="stylesheet" type="text/css"
      href="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.css"/>
<script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.js"></script>
<script>
    window.addEventListener("load", function () {
        window.cookieconsent.initialise({
            "palette": {
                "popup": {
                    "background": "#252e39"
                },
                "button": {
                    "background": "#14a7d0"
                }
            },
            "theme": "classic",
            "content": {
                "link": "Learn more",
                "href": "/cookies"
            }
        });

        // Enable tooltips for all elements
        refreshTooltips();

        // Make sure selectpicker is enabled
        $(".selectpicker").selectpicker();
    });

    function refreshTooltips() {
        console.log('refreshing tooltips');
        $('[data-toggle="tooltip"]').tooltip();
        // $('[data-toggle="tooltip"]').tooltip({trigger: 'manual'}).tooltip('show');
    }
</script>