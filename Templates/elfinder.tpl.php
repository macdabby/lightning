<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>elFinder 2.1.x source version with PHP connector</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2" />

    <!-- jQuery and jQuery UI (REQUIRED) -->
    <link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="/js/lightning.min.js" language="javascript"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

    <!-- elFinder CSS (REQUIRED) -->
    <link rel="stylesheet" type="text/css" href="/js/elfinder/css/elfinder.min.css">
    <link rel="stylesheet" type="text/css" href="/js/elfinder/css/theme.css">

    <!-- elFinder JS (REQUIRED) -->
    <script src="/js/elfinder/js/elfinder.min.js"></script>

    <!-- elFinder initialization (REQUIRED) -->
    <script type="text/javascript" charset="utf-8">
        // Documentation for client options:
        // https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
        $().ready(function(){
            lightning.fileBrowser.init();
        });
    </script>
</head>
<body style="margin:0; padding:0;">
<!-- Element where elFinder will be created (REQUIRED) -->
<div id="elfinder" style="height:100%; border:none;"></div>
</body>
</html>
