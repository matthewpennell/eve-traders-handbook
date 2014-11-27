<!DOCTYPE html>

<html class="no-js" lang="en">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <title>EVE Traders Handbook</title>

        <meta name="description" content="EVE Traders Handbook is a reference book for industrialists and traders in the MMORPG EVE Online.">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="apple-touch-icon" href="/ico/apple-touch-icon.png">

        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700">
        <link rel="stylesheet" href="/c/normalize.css">
        <link rel="stylesheet" href="/c/eth.css">

        <script src="/j/vendor/modernizr-2.8.3.min.js"></script>

    </head>

    <body>

        <header>
            <div class="container">
                <h1><a href="/">EVE Traders Handbook</a></h1>
                <nav class="header-nav">
                    <ul role="navigation">
                        <li><a href="/settings">Settings</a></li>
                        <li><a href="/logout">Log out</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <div class="content">
            <div class="container">
                @yield('content')
            </div>
        </div>

        <footer>
            EVE Traders Handbook is licensed under the GNU General Public License (GPL-3.0). If you enjoy using this software, please consider making an in-game ISK donation to <a href="https://gate.eveonline.com/Profile/Shei%20Bushaava">Shei Bushaava</a>.
        </footer>

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
