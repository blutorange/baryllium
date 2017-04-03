<html>
    <head>
        <title><?=$this->e($title ?? 'Initial setup successful')?></title>
        <meta charset="UTF-8">
        <style>
            label,input,select {
                display: block;
                width: 100%;
            }
            label {
                margin-bottom: 1em;
            }
            span.required-star {
                color: red;
                font-weight: 400;
            }
        </style>
    </head>
    <body>
        <p id="t_setup_redirect_user">
            Configuration file exists now, you may <a href="./setup_admin.php">
            proceed to user setup.</a>
        </p>
    </body>
</html>