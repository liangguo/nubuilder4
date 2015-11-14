<?php

    require_once('nucommon.php');

    $checkInstalledQRY = nuRunQuery("SELECT * FROM zzzzsys_setup ");
    $installHTML = '';
    $siteName = '';
    if(db_num_rows($checkInstalledQRY) == 0){
        $installHTML = 'nuBuilder is not installed. <a href="nusetup.php">Setup nuBuilder.</a>';
    } else {
        $checkInstalledOBJ = db_fetch_object($checkInstalledQRY);
        $siteName = $checkInstalledOBJ->set_site_name;
    }

?>

<html>
    <head>
        <script src="lib/jquery-2.1.4.js"></script>
        <script src="lib/spin.min.js"></script>
        <script type="text/javascript">

            $(document).ready(function(){
                $("#username, #password").keyup(function(event){
                    if(event.keyCode == 13){
                        $("#loginbutton").click();
                    }
                });
                $('#username').focus();
            });

            function login(){
                $('#loader').html('');
                var opts = {
                  lines: 13 // The number of lines to draw
                , length: 7 // The length of each line
                , width: 5 // The line thickness
                , radius: 3 // The radius of the inner circle
                , scale: 1 // Scales overall size of the spinner
                , corners: 1 // Corner roundness (0..1)
                , color: '#000' // #rgb or #rrggbb or array of colors
                , opacity: 0.25 // Opacity of the lines
                , rotate: 0 // The rotation offset
                , direction: 1 // 1: clockwise, -1: counterclockwise
                , speed: 1 // Rounds per second
                , trail: 60 // Afterglow percentage
                , fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
                , zIndex: 2e9 // The z-index (defaults to 2000000000)
                , className: 'spinner' // The CSS class to assign to the spinner
                , top: '300px' // Top position relative to parent
                , left: '50%' // Left position relative to parent
                , shadow: false // Whether to render a shadow
                , hwaccel: false // Whether to use hardware acceleration
                , position: 'absolute' // Element positioning
                };
                var spinner = new Spinner(opts).spin($('#loader')[0]);
                $.ajax({
                    url: 'api/nulogin.php',
                    data: {
                        username: $('#username').val(),
                        password: $('#password').val()
                    },
                    dataType: 'json'
                }).done(function(data){
                    $('.spinner').remove();
                    if(data.success){
                        alert('LOGGED IN sessionID of: '+data.session_id);
                    } else {
                        $('#loader').html('Incorrect username / password.');
                    }
                }).fail(function(){
                    $('.spinner').remove();
                    alert('Could not connect to nuBuilder.');
                });
            }

        </script>
    </head>
    <body>
        <div id="installlink"><?php print $installHTML; ?></div>
        <div id="main" style="text-align: center;width: 500px;margin-left: auto;margin-right: auto;height: 220px;margin-top:100px;background-color:#D4D4D4;">
            <img src="img/logo.png" style="padding-bottom: 20px; padding-top: 20px;" />
            <div id="loginfields">
                <table style="margin-left:auto; margin-right:auto;">
                    <tbody>
                        <tr><td colspan="2" style="text-align: center;"><?php print $siteName; ?></td></tr>
                        <tr>
                            <td>
                                <label for="username">Username: </label>
                            </td>
                            <td>
                                <input id="username" type="text" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="password">Password: </label>
                            </td>
                            <td>
                                <input id="password" type="password" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button id="loginbutton" onclick="login();" style="margin-left:auto;margin-right:auto;margin-top:10px;">Login</button>
                <div id="loader"></div>
            </div>
        </div>
    </body>
</html>