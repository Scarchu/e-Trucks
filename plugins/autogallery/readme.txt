/*********************************************************************************************
 * e107 PLUGIN: Auto Gallery
 * VERSION:     3.01
 * DESCRIPTION: A media/image gallery, where galleries are based on a directory structure. 
 *              Thumbnails are automatically generated through Imagemagick or GD.
 * WRITTEN BY:  Mr_Visible (www.cerebralsynergy.com)
 * DATE:        27-Aug-2007
 *
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 ********************************************************************************************/

-------------------------------------
 KNOWN BUGS
-------------------------------------
* 'Breakout' V3Arcade game doesn't work with top scores
* Issues with flvplayer in internet explorer

-------------------------------------
 DESCRIPTION
-------------------------------------
A simple to use media and image gallery that bases galleries off a file folder structure. Auto Gallery is one of the simplest ways to add media to your website: It can display images, flash animations, flash games, movies and sound files.

See it in action here: http://www.cerebralsynergy.com/e107_plugins/autogallery/autogallery.php

Auto Gallery is designed around ease of use and administration. It's main objective is allow administrators to copy, move, upload, rename and delete files and folders in a directory tree, and for these actions to be reflected instantly on the website. You can add images, movies, animations, games, and sound files simply by uploading them through a FTP client. Its priority is simplicity.

However, Auto Gallery has gained some powerful features over the years. Although it won't offer complex features found in galleries such as Coppermine, it does deliver functionality to rival many galleries available. A list of major features is detailed below:

Image Manipulation
 * Automatic image thumbnailing
 * Image watermarking
 * Image rotating
 * Animated GIF Support
 * Works with GD, GD2 and ImageMagick
 
Security
 * Leech protection (hot-linking)
 * Automatic watermarking (image overlay) of images
 * Admin logs
 * Viewing, uploading, administration and commenting can be controlled through user classes for individual galleries

Meta Data
 * Descriptions can be set for media files and galleries
 * Latest media page
 * Latest comments page
 * View and email hit counters
 * XML data backend - Easy to edit, move, delete and copy without e107 (e.g. via FTP)
 
User Interactions
 * Email to friend page
 * User comments (controlled by user classes)
 * User ratings (controlled by user classes)
 * Media and gallery search page
 * Users can submit media directly to galleries, or through an admin approval process (depending on their user class membership)
 * Timer-based slide shows
 * Optional embed code/link text boxes

Administration
 * Context administration menu allows gallery admins to:
   - Delete media files/galleries
   - Rename media files/galleries
   - Move media files/galleries
   - Manually watermark images
   - Rotate images
   - Change user access to a gallery
   - Create new galleries
   - Change descriptions of media files/galleries
   - Set thumbnails for media files
   - Set thumbnails for galleries (known as default images)
 * Operations can be performed on one or many files at a time

Miscellaneous
 * MANY options to control Auto Gallery's look and feel
 * Arcade integration - top scores table
 * XHTML Compliant
 
Supported Media
 * Images (bmp, jpg, gif and png)
 * Flash animations and games (swf)
 * Flash video (flv) - like youtube etc.
 * Apple Quicktime movies (mov)
 * Real Media movies (rm)
 * Windows Media Player movies (wmv, avi, mpg, divx)
 * Audio (mp3, wma)
  
AUTOGALLERY ARCADE:
Thanks to SpooK for his help on this. Although all flash games can be played by Auto Gallery, not all games will work with the top scores table. AG supports IBPro (Invision Power Board Forum Arcade) and V3Arcade games. Apparently there are over 1000 supported games available. Below are some URLs where these can be downloaded:

Site 1: http://crazydons.free.fr/files/jeux/packs/vigilante/
Site 2: http://origon.dk

REPORTING BUGS:
Follow instructions in the 'Report Bug' admin section. If you can't for some reason, report bugs here: http://cerebralsynergy.com/bugs

CHANGE LOG:
http://cerebralsynergy.com/e107_plugins/autogallery/changelog.txt

-------------------------------------
 INSTALLATION INSTRUCTIONS
-------------------------------------
1. Unzip archive
2. Move the autogallery directory into your e107 plugin
3. From the e107 plugin manager (e.g. http://<YOUR DOMAIN>/e107_admin/plugin.php) Press the 'Install' button for Auto Gallery
4. Run the Auto Gallery chmod script (e.g. http://<YOUR DOMAIN>/e107_plugins/autogallery/admin_dochmod.php)

-------------------------------------
 UPGRADE INSTRUCTIONS
-------------------------------------
1. Unzip archive
2. Overwrite all existing files in the following directories:

   * <AG_BASE>/ (Auto Gallery base directory)
   * <AG_BASE>/Languages/
   * <AG_BASE>/Images/

3. From the e107 plugin manager (e.g. http://<YOUR DOMAIN>/e107_admin/plugin.php) Press the 'Upgrade' button for Auto Gallery
4. Run the Auto Gallery chmod script (e.g. http://<YOUR DOMAIN>/e107_plugins/autogallery/admin_dochmod.php)

NOTE: If there are any issues with this process, overwrite ALL files and folders with ones in this package.

-------------------------------------
 INSTRUCTIONS
-------------------------------------
Place your images in this folder:  /e107_plugins/autogallery/Gallery
The Link to the main gallery is:   /e107_plugins/autogallery/autogallery.php

-------------------------------------
 FURTHER INFO/HELP
-------------------------------------
Auto Gallery Support: http://www.cerebralsynergy.com
Auto Gallery Bugs: http://cerebralsynergy.com/bugs
