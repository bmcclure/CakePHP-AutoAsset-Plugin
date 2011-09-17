AutoAsset Plugin
================

A CakePHP 2.x (and 1.x) plugin to help easily manage and load CSS and JS files asynchronously.


Why?
====

Because.

Asynchronously loading JS and CSS files can be a very powerful alternative to concatenating,
compressing, and caching your files into an unreadable mess. There are a lot of solutions which
do the latter for CakePHP, but (almost) none that do the former. That's where AutoAsset comes in.


Background
==========

There are a lot of CSS and JavaScript helpers (and some plugins) available which are designed 
to help users load specific controller/action assets automatically. Initially I implemented that
functionality myself with a few lines of PHP.

But then I started wanting to lazy-load assets using JavaScript, and suddenly almost all of the 
Cake goodness went out the window... I was managing my CSS and JavaScript files from within a main 
JavaScript file, with no ties what-so-ever to my CakePHP installation.

So I found an amazingly simple script loader (https://github.com/ded/script.js) and decided to couple
it with my existing code to have CakePHP output the required JavaScript to lazy-load the necessary files.
This was *almost* what I wanted.

And then, AutoAsset was born. I created a component which helps to automatically locate and organize assets 
according to certain optional settings provided to it. I expanded $script (The Javascript loader) to also
include $css, a CSS lazy-loader and a couple of helper functions ($url and $namespace), and a helper to tie
it all into your views and layouts.


Requirements
============

*   CakePHP 2.0 Beta or greater (CakePHP 1.3 is supported in the 1.x branch)
*   PHP 5.2+ (You should already have it if you're using CakePHP 2.x!)


Installation
============


Manual
------

1.   Download the plugin: http://github.com/bmcclure/AutoAsset/zipball/master
2.   Unzip the downloaded file to your CakePHP app's 'Plugin' folder
3.   Rename the unzipped folder to 'AutoAsset'


Directly From GitHub
--------------------

Simply clone this repository to your CakePHP application under app\Plugin\AutoAsset


For CakePHP 2.0 users
---------------------

No matter how you install the plugin, if you are using CakePHP 2.0 you need to make sure it is enabled in
your app/Config/bootstrap.php file.

Either use:

    CakePlugin::load('AutoAsset'); //Loads just this plugin

Or:

    CakePlugin::loadAll(); // Loads all plugins at once


Usage
=====


Placing your media files
------------------------

You can choose the directory to store your controller and action files in. You can use either controller 
files, or action files, or both. To use the default directory which requires no additional configuration,
place your files like this:

    /app/webroot/
                 css/
                     controllers/
                 js/
                     controllers/

Underneath controllers/ you can place asset files for any of your controllers. For a 'users' controller
you could have files here:

    /app/webroot/css/controllers/users.css
    /app/webroot/js/controllers/users.js

You can also utilize separate CSS and/or JS files for specific controller actions, like this:

    /app/webroot/css/controllers/users/index.css
    /app/webroot/js/controllers/users/index.js

You don't need to create a controller media file to be able to use an action media file. If you use both,
the controller file will start loading before the action file, but since they are loaded asynchronously
they are not guaranteed to finish loading in the same order. Keep reading to learn about how to properly
define dependencies for your JS and CSS files.

Finally, you can place any other JS files anywhere underneath your webroot/js directory, and any other CSS
files underneath your webroot/css directory, and have AutoAsset place them in your layout or load them 
asynchronously.

You might place your JS libraries in webroot/js/libs, for example. AutoAsset will then be able to reference
library paths such as 'libs/jquery-1.6.min'.


Loading the component
---------------------

Next, load the AssetGatherer component in your AppController. Your $components array might look like this:

    public $components = array('AutoAsset.AssetGatherer');

Or maybe you want to provide a couple of options to AssetGatherer to make it even more useful:

    public $components = array(
        'AutoAsset.AssetGatherer' => array(
            'asyncJs' => 'app',
            'requiredJs' => array('libs/modernizr-1.7-custom.min', 'libs/selectivizr'),
        ),
    );

The full set of options you can provide to the component (and their defaults) are:

*   'asyncJs' (Default: 'bootstrap')
    
    Indicates which JS files should be loaded asynchronously. This is by default a string ('bootstrap')
    used to load the JS file from your webroot /js/bootstrap.js where you can lazy-load all of your other
    JavaScript and CSS. You can also provide your own string or array of files to load.

*   'asyncCss' (Default: null)

    Indicates which CSS files should be loaded asynchronously. By default this is null so that you can simply
    include the CSS files you'd like within bootstrap.js.

*   'requiredJs' (Default: null)

    Indicates which JS files should be loaded first, in the head section of your layout. This is usually used
    for prerequisites that you know will always have to be loaded, and that don't overly slow down the loading
    of your pages. Frequently you would put Modernizr and Selectivizr here, if you use them.

*   'requiredCss' (Default: null)

    Indicates which CSS files should be loaded first, in the head section of your layout. This is often used
    to load your "main" CSS file which controls the appearance of your site. Any CSS which doesn't need to be loaded
    already when the page is first displayed should instead go in 'asyncCss' to help speed up your site.
    
*   'globals' (Default: null)

    Can contain an associative array of key and value pairs that will be output as Javascript variables available
    to all other JavaScript files. Be careful not to overwrite any important variables here. You can choose where
    in your script these are output using the AssetLoaderHelper.

*   'controllersPath' (Default: 'controllers')

    Indicates the path relative to both your /js and /css folders where your controller/action JS and CSS files
    reside. This can be null to turn off controller/action auto-loading functionality. Alternatively, you can 
    leave it as-is and simply not create or utilize the /js/controllers and /css/controllers path, and
    controller/action CSS and JS file inclusion will also be disabled.

*   'scriptJs' (Default: '/auto_asset/js/script.min')

    Indicates the path to the script.js loader for Javascript. You can point to your own version, or set to 
    null to not include script.js (which will essentially disable all $script() calls and make most of this 
    plugin useless.

*   'cssJs' (Default: '/auto_asset/js/css')

    Indicates the path to the css.js loader for CSS files. You can point to your own version, or set to
    null to not include css.js (which will disable all lazy-loading of CSS files).

*   'namespaceJs' (Default: '/auto_asset/js/namespace')

    Indicates the path to the namespace.js helper function for Javascript. You can point to your own version, or
    set to null to not include namespace.js (which will disable the $namespace() function in your JS files).

*   'urlJs' (Default: '/auto_asset/js/url')

    Indicates the path to the url.js helper function for Javascript. You can point to your own version, or set
    to null to not include url.js (which will disable the $url() function in your JS files).

Finally, in your AppController's beforeFilter() callback, add the following line:

    $this->set('assets', $this->AssetGatherer->getAssets());

This will provide the $assets array to your views.


Loading the helper
------------------

Also in your AppController, add the AssetLoader helper to your $helpers array. It might look like this:

    public $helpers = array('AutoAsset.AssetLoader');

The AssetLoader helper doesn't take any settings.


Configuring your layout
-----------------------

If you are using AssetGatherer's 'requiredJs' or 'requiredCss' options, add the following to your layout 
somewhere within the head tag (and before you load other JS and CSS files):

    if (isset($assets)) {
        echo $this->AssetLoader->required($assets);
    }

And finally near the bottom, usually right before the closing body tag, add the following:

    if (isset($assets)) {
        echo $this->AssetLoader->globals($assets);
        echo $this->AssetLoader->load($assets);
    }
    
You can call the functions anywhere in your layout that you would like, but it is recommended to follow the
above pattern for maximum performance.

The main exception is if any of your 'requiredJs' files rely on your defined 'globals'. In that case, call the
globals() function before the required() function.

Also note that if you do not call required() before calling load(), load() will also load all of the required files
for you. Thus, if you'd prefer to load everything before the closing body tag, you can simply omit the required()
function call from the head.


The end result
--------------

Now that everything is hooked up, you don't need to touch your code to add controller/action JS or CSS files,
and they won't slow down the loading of your site.

But don't let the magic stop there! Read on...


Included JavaScript Helpers
===========================

AutoAsset includes a number of special global JavaScript functions which have no dependencies that you can use
throughout your applications to help simplify your scripts:

$script
-------

A tiny asynchronous JavaScipt loader. AutoAsset uses it to load your controller/action files, and you can use
it yourself to load any other JS files that you'd like.

It also provides an excellent way to manage dependencies when asynchronously loading JS files.

Read the full documentation at $script's repository:
https://github.com/ded/script.js

For a quick jumpstart, let's load jquery from within our app's main JavaScript file, in this case bootstrap.js:

    // Load jQuery asynchronously from webroot/js/libs/jquery.js and refer to it by the name 'jquery'
    $script('libs/jquery', 'jquery');

Then later, in another file, we can use jQuery easily like this:

    // Callback to run when jQuery finishes loading
    $script.ready('jquery', function() {
        $(document).ready({
            // More jQuery goodness...
        });
    });

$script is a whole lot more powerful than that, though, so I encourage you to visit its own project page on GitHub
and familiarize yourself with its functionality.

$css
----

$css is my take on the $script of the CSS world... I know, hard to imagine, right?

Use it to lazy-load CSS files just like $script loads JS files, so you can do stuff like this:

    // Load a script and its related CSS data at the same time
    $css('libs/jquerytools/overlay', 'overlay');
    $script('libs/jquerytools/overlay', 'overlay');

Some work is still required to get CSS callbacks working properly.

$namespace
----------

A simple function which allows you to easily namespace your JavaScript libraries to keep everything
neat and tidy.

Namespacing your scripts is as simple as writing something like this:

    $namespace('SingularityShift.Util').OverlayManager = function() {
        this.close = function(rel) {
            // This is a public function accessible at SingularityShift.Util.OverlayManager.close(rel);
        }

        var showInternal = function(rel) {
            // This is an internal function, callable only from within the same namespace.
        }
    }

$url
----

An attempt at a cake-like way to resolve URLs for JavaScript.

To give it a hand, if you're using HTML5 add the following within the head section of your layout:

    <base href="<?php echo Router::url('/', true); ?>">

If you don't add the base tag, or if you're not using HTML5 yet, the $url function will try to figure out
the base URL on its own.

Now you can simply call:

    $url('/users/add') // Returns something like http://yourdomain.com/users/add in this case)

which will return the absolute URL to the 'add' action of the 'users' controller, wherever that may be.

You can also pass a full, absolute URL into $url and it will simply spit the URL back out at you, meaning you can
pass ALL urls through $url() and you'll always get a valid, absolute URL returned.

The magic is thanks to the "base" tag you added earlier which points $url to the root of your CakePHP installation.


Final Notes
===========

More to come!