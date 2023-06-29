<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Elements in HTML</title>
        <!-- Embed elements Elements via Web Component -->
        <script src="{{ config('auto-doc.global_prefix') }}/auto-doc/web-components.min.js"></script>
        <link rel="stylesheet" href="{{ config('auto-doc.global_prefix') }}/auto-doc/styles.min.css">
    </head>

    <body>
        <elements-api apiDescriptionUrl="/auto-doc/documentation" router="hash" layout="sidebar"/>
    </body>
</html>
