<!DOCTYPE html>

<html lang="en">

    <head>

        <meta charset="utf-8">

        <title>EVE Traders Handbook</title>

        <style>
            * {
                font: 16px/20px "Helvetica Neue", Arial, Helvetica, sans-serif;
                color: #333;
                background: #f2f4f6;
            }
            h1 {
                font-size: 30px;
            }
            h2 {
                font-weight: bold;
                font-size: 20px;
            }
            .form-field {
                margin: 0 0 20px;
            }
            .form-label {
                display: block;
            }
            .form-input {
                width: 500px;
            }
            .items-lost {
                clear: left;
            }
            .items-lost th, .items-list td {
                padding: 5px 10px;
                min-width: 200px;
            }
            .items-lost th {
                background: #ccc;
                font-weight: bold;
                text-align: left;
            }
            .items-lost tr:nth-child(even) td {
                background: #ddd;
            }
            .filter {
                float: left;
                padding: 10px 10px 10px 0;
            }
        </style>

    </head>

    <body>

        <h1>EVE Traders Handbook</h1>

        @yield('content')

        <script>

            var filters = document.getElementsByTagName('input');

            for (var i in filters) {
                this.onchange = function (evt) {
                    document.getElementsByClassName('filters')[0].submit();
                };
            }

        </script>

    </body>

</html>
