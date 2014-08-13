<?php
//auto generated file (compiled at 2014-07-31 13:20:05)

class indexTpl
{
    public $arr = array();
    public function render_page ($_V)
    {
        if(is_array($_V) && (array_keys($_V) !== range(0, count($_V) - 1))) {
            foreach($_V as $k=>$v){$$k = $v;}
        }

        $_P='<head><title>' ;   $_P.= $projectName;   $_P.=' backend-login on ' ;   $_P.= $servername;   $_P.='</title><meta charset="utf-8"><meta name="robots" content="none"><meta http-equiv="cache-control" content="max-age=0"><meta http-equiv="cache-control" content="no-cache"><meta http-equiv="expires" content="0"><meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT"><meta http-equiv="pragma" content="no-cache"><meta http-equiv="content-script-type" content="text/javascript"><meta http-equiv="content-style-type" content="text/css"><meta name="viewport" content="width=device-width, height=device-height, initial-scale=1"><link rel="icon" type="image/png" href="inc/img/icon.png"><link rel="stylesheet" type="text/css" href="inc/login/css/styles.css"><script src="inc/js/modernizr.js"></script><script>
        Modernizr.addTest(\'json\', function () {
            return window.JSON
                    && window.JSON.parse
                    && typeof window.JSON.parse === \'function\'
                    && window.JSON.stringify
                    && typeof window.JSON.stringify === \'function\';
        });
    </script><script src="' ;   $_P.= $jquerypath;   $_P.='/plugins/json3.min.js"></script></head><body>

<h2 class="warning no-js">' ;   $_P.= $L["javascript_not_activated"];   $_P.='!</h2>
<h2 class="warning ie6">IE 6: ' ;   $_P.= $L["your_version_of_internet_explorer_may_not_work"];   $_P.='</h2>
<h2 class="warning ie7">IE 7: ' ;   $_P.= $L["your_version_of_internet_explorer_may_not_work"];   $_P.='</h2>
<h2 class="warning ie8">IE 8: ' ;   $_P.= $L["your_version_of_internet_explorer_may_not_work"];   $_P.='</h2>


';  if( $error ){   $_P.='
        <div id="error">' ;   $_P.= $error;   $_P.='</div>
'; }   $_P.='


<div id="msg">
    <img src="inc/login/css/spinner-mid.gif"> ' ;   $_P.= $L["please_wait"];   $_P.='
    <hr></div>

<span id="head_right" style="float:right">
	<img src="inc/img/logo.png"></span>

<form id="form" style="display:none" method="post" action="backend.php" enctype="multipart/form-data">

    <input type="hidden" id="lang" name="lang" value="' ;   $_P.= $lang;   $_P.='"><input type="hidden" id="client" name="client" value="no-js">

    ' ;   $_P.= $L["login"];   $_P.=' <br>

    ';  if( $projectCount==1 ){   $_P.='
        <input type="hidden" id="project" name="project" value="' ;   $_P.= $projects["0"];   $_P.='">
    '; }  else{   $_P.='
        <p>
            <label for="project">' ;   $_P.= $L["project_name"];   $_P.='</label>
            <input type="text" required="required" id="project" name="project" placeholder="' ;   $_P.= $L["project_name"];   $_P.='" value="' ;   $_P.= $projectName;   $_P.='"><br></p>
    '; }   $_P.='

    <p>
        <label for="name">' ;   $_P.= $L["user_name"];   $_P.='</label>
        <input required="required" type="text" id="name" name="name" placeholder="' ;   $_P.= $L["user_name"];   $_P.='"></p>

    <p id="passline">
        <label for="pass">' ;   $_P.= $L["password"];   $_P.='</label>
        <input required="required" type="password" autocomplete="off" id="pass" name="pass" placeholder="' ;   $_P.= $L["password"];   $_P.='"></p>

    <p>
        <button type="submit" title="login" style="float:right;margin-right:-10px;">
            <span style="background-position: -64px -144px"></span>
        </button>

        <button id="register_button" type="button" title="' ;   $_P.= $L["register"];   $_P.='">
            <span style="background-position: -144px -96px"></span>
        </button>

        <button id="reset_button" type="button" title="' ;   $_P.= $L["forgot_password"];   $_P.='">
            <span style="background-position: -80px -96px"></span>
        </button>

        <button id="sethash_button" type="button" title="' ;   $_P.= $L["bookmark"];   $_P.='">
            <span style="background-position: -224px -96px"></span>
        </button>
    </p>
    <p id="additional_buttons"></p>
</form>

<img id="captcha" src="inc/login/css/blank.png"><img id="cheat" src="inc/login/css/blank.png"><link rel="stylesheet" type="text/css" href="' ;   $_P.= $jquerypath;   $_P.='/themes/smoothness/jquery-ui.css"><script src="' ;   $_P.= $jquerypath;   $_P.='/jquery.min.js"></script><script src="' ;   $_P.= $jquerypath;   $_P.='/jquery-ui.min.js"></script><script>

    var msgNo = 0;//
    var logout = ' ;   $_P.= $logout;   $_P.=';

    /**
     * show processing for Logout-Hooks
     * @param str
     */
    function msg(str) {
        var b = $(\'#msg\');
        b.show();
        if (!str) return;
        b.html(b.html() + \'<span title="\' + str + \'">(\' + msgNo + \') \');
        --msgNo;
        if (msgNo <= 0) {
            b.style.backgroundColor = \'#cfc\';
            b.html(\'all Jobs done!<hr\' + b.html().split(\'<hr\').pop());
        }
    }


    /**
     * (try to) load a script that may add / adapt some settings on the login-page
     * @param name
     */
    function loadProjectJs(name) {
        if (name.length > 2) {
            project = name;
            $.getScript(\'../projects/\' + name + \'/extensions/default/login.js\');
        }
    }

    $(document).ready(function () {
        // get the Project-Name if set by $_GET
        project = $(\'#project\').val();

        ';  if( $localhost ){   $_P.='
        // on localhost, we can provide existing projects as autocomplete
        var projects =  ';   $_P.= json_encode($projects);   $_P.='
        $(\'#project\').autocomplete({ source: projects });
        '; }   $_P.='
        // clear the window.name (Storage for User-Settings)
        top.window.name = null;
        // show the main Form (hidden if JS is not activated)
        $(\'#form\').show();

        // hide Labels if html5-Placeholders are available
        if (Modernizr.input.placeholder) {
            $(\'label\').hide()
        }

        // Listener to get a new captcha-image
        $(\'#captcha\').on(\'click\', function () {
            $(this).attr(\'src\', \'inc/php/captcha.php?x=\' + Math.random())
        });

        // Listener to switch all input-types (hidden, password) to normal text-inputs
        $(\'#cheat\').on(\'click\', function () {
            $(\'input\').attr(\'type\', \'text\')
        });

        // add screen width/height to modernizr-object
        Modernizr.win = {};
        Modernizr.win[\'width\'] = $(window).width();
        Modernizr.win[\'height\'] = $(window).height();
        // calculate the time difference between client and server
        Modernizr.tdiff = Math.round(Date.now() / 1000) - ' ;   $_P.= $timestamp;   $_P.=';

        // transfer modernizr-detection to a hidden field
        $(\'#client\').val(JSON.stringify(Modernizr));

        // "bookmarkable" credentials (Hashes are invisible for the server)
        h = window.location.hash.substr(1);
        if (h.length > 0) {
            var p = h.split(\'&\');
            for (var i = 0, j = p.length; i < j; ++i) {
                var a = p[i].split(\'=\'), el = $(\'#\' + a[0]);
                if (el) el.val(a[1]);
            }
        }
        else// clear inputs if there is no hash
        {
            window.setTimeout(function () {
                $(\'#pass\').val(\'\');
                $(\'#mail\').val(\'\');
            }, 1000);
        }

        // Listener to save credentials as Bookmark-Url
        $(\'#sethash_button\').on(\'click\', function () {
            var q = confirm(\'' ;   $_P.= $L["bookmark"];   $_P.='\');
            if (q) window.location = \'?project=\' + $(\'#project\').val() + \'#name=\' + $(\'#name\').val() + \'&pass=\' + $(\'#pass\').val();
        })

        // warn the user if capsLock is activated
        $(\'#pass\').on(\'keypress\', function (e) {
            var s = String.fromCharCode(e.which);
            if (s.toUpperCase() === s && s.toLowerCase() !== s && !e.shiftKey) {
                $(\'#msg\').html(\'' ;   $_P.= $L["capsLock_is_active"];   $_P.='\');
                $(\'#msg\').show();
            }
            else {
                $(\'#msg\').hide();
            }
        });


        // (re)load the additional project-specific functions
        $(\'#project\').on(\'blur\', function () {
            loadProjectJs($(this).val())
        });

        loadProjectJs(project);

        // Fetch background-image for desktop-devices
        $.getScript(\'inc/login/js/jquery.backstretch.js\', function () {
            var now = new Date();
            $.getScript(\'inc/login/x_of_the_day.php?t=\' + now.getDay());
        });
    });

    /**
     * Listen to key events
     * @param e
     * @constructor
     */
    function KeyCheck(e) {
        var KeyID = (window.event) ? event.keyCode : e.keyCode;
        switch (KeyID) {
            //alt => load a new captha image
            case 18:
                $(\'#captcha\').attr(\'src\', \'inc/php/captcha.php?x=\' + Math.random());
                break;
            //ctrl not used atm
            case 17:

                break;
        }
    }

    // Activate key listener
    document.onkeyup = KeyCheck;

</script></body>';

        return  $_P;
    }
}