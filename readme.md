Google Analytics module for ci-bonfire.

Based on the library found here http://codeigniter.com/forums/viewthread/128945/
Based on the Bonfire Module created by Shobo, updated to use database settings instead of a config file.

INSTALL
===========================================
Drop analytics folder in modules folder.
Run migration, set permissions and you're ready to use it.

CONFIGURING
===========================================

You'll need a valid google analytics account to use 
this module.

Head over to settings->analytics and set your GA 
username and password.

The profile field is not used yet.


USING MODULE TO DISPLAY FOOTER CODE
===========================================

To Display the Javascript code in the footer just add the following line of code in your Template file near the bottom.


    <?php  echo Modules::run('analytics/analytics/show_gcode'); ?>


