# Folder "login"

This folder contains all files used by the login-page "backend/index.php"

Files & folders

* css/
    * blank.png => used as a placeholder for the captcha image
    * spinner_mid.gif => not used atm
    * styles.css => styles used
* js/
    * jquery.backstretch.js => lib to load/create a fullscreen background image
* locales/
    * *xy*.php => translation arrays
* tpl/
    * indexTpl.xhtml => template, containing all html-code used by by the login-page
    * indexTpl/
        * tpl.php => compiled template
* login_src.php => array of pictures 
* x_of_the_day.php => simple php function loaded as javascript to generate the "pic of the day"

Please note that the login-page needs jquery + jquery-ui located at "vendor/cmskit/lib-jquery-ui". 
In addition the theme "smoothness" is used/required!
