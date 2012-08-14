AutoAsset Plugin
================
A full-featured and extensible asset management plugin for CakePHP 2.x

Important Information
=====================

AutoAsset just underwent a full rewrite and its documentation has not yet been updated. In 
addition, some functionality is as yet untested. If you're not comfortable poking around the
source code to figure out how to use the new version, then it would be best to wait until this
documentation has been updated.

Some of the cool new features are:
*  New OO library, 'AssetLib', included and used throughout AutoAsset.
*  Assets are assigned to any number of AssetBlock objects.
*  AssetBlocks contain custom settings for the block and are rendered in a layout or view
*  AssetRenderer objects are responsible for outputting AssetBlocks (and individual assets)
*  AssetGathererComponent is now AssetCollectorComponent and works in a much more OO-fashion
*  AssetLoaderHelper is now AssetRendererHelper and allows for much more flexibility and extensibility
*  AssetLib has a small library of exceptions thrown to help recover from exceptional circumstances

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

*   CakePHP 2.0 or greater (CakePHP 1.3 is supported in the outdated 1.x branch)
*   PHP 5.3+ (Might work on 5.2)


Installation
============


Manual
------

1.   Download the plugin: http://github.com/bmcclure/AutoAsset/zipball/master
2.   Unzip the downloaded file to your CakePHP app's 'Plugin' folder
3.   Rename the unzipped folder to 'AutoAsset'


Directly From GitHub
--------------------

Simply clone this repository to your CakePHP application under app\Plugin\AutoAsset. If your app is 
version-controlled with Git already, then you can add AutoAsset as a submodule.


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
    
*   'asyncLess' (Default: null)

    Works the same as asyncCss, only it loads a .less file using LESS instead.

*   'requiredJs' (Default: null)

    Indicates which JS files should be loaded first, in the head section of your layout. This is usually used
    for prerequisites that you know will always have to be loaded, and that don't overly slow down the loading
    of your pages. Frequently you would put Modernizr and Selectivizr here, if you use them.

*   'requiredCss' (Default: null)

    Indicates which CSS files should be loaded first, in the head section of your layout. This is often used
    to load your "main" CSS file which controls the appearance of your site. Any CSS which doesn't need to be loaded
    already when the page is first displayed should instead go in 'asyncCss' to help speed up your site.
    
*   'requiredLess' (Default: null)

    Works the same as the requiredCss option above, only for .less files.
    
*   'globals' (Default: null)

    Can contain an associative array of key and value pairs that will be output as Javascript variables available
    to all other JavaScript files. Be careful not to overwrite any important variables here. You can choose where
    in your script these are output using the AssetLoaderHelper.
    
*   'meta' (Default: null)

    Contains an array of arrays containing three values (the same values that HtmlHelper->meta() accepts. The best
    way to use this setting is to utilize AssetGatherer's meta() function, since it offers a lot of helpful
    functionality before adding the parameters to this setting.
    
    The suggested way to use it is to call AssetGatherer->meta() the exact same way you would with HtmlHelper, but
    do it in your controller's beforeFilter (or in the AppController).
    
    There are many shortcuts for the available meta tags that make it very handy to use. They work much like the
    existing shortcuts for 'icon', 'keywords', 'description', etc. You can use the existing shortcuts as well as 
    new shortcuts through the same function.
    
    The new shortcuts are:
    *   'author'
    
        Use 'author' as the first value, and a URL for the second value. If the URL is left null, '/humans.txt'
        will be used as a convenient default.
        
    *   'viewport'
    
        Use 'viewport' as the first parameter, and supply the 'content' value as the second parameter. If the
        second parameter is left null, 'width=device-width, initial-scale=1' is used as a standards-based default.
        
    *   'sitemap'
        
        Use 'sitemap' as the first parameter, and the URL to your sitemap file as the second. If the URL is left
        null, '/sitemap.xml' will be supplied as a default. You can optionally supply a custom 'title' in the
        options array (third parameter).
        
    *   'search'
        
        Supply the meta tag needed for an OpenSearch definition file. Use 'search' as the first paraemter, and 
        the path to your opensearch definition file as the second. If the path is left null, '/opensearch.xml'
        will be used as a default. You can also supply a custom 'title' in the options parameter (third param)
        if you do not wish to use 'Search'.
        
    *   'canonical'
        
        Supply the coninical URL as the second parameter (This might be the current URL with parameters stripped
        off, for example.
        
    *   'shortlink'
        
        Ideally, this should contain the URL of the shortest possible link to get to the current page. Supply
        it for the second parameter.
        
    *   'pingback'
        
        Supply your pingback service URL in the second parameter.
        
    *   'imagetoolbar'
        
        Supply 'false' in the second parameter to turn the IE6 image toolbar off.
        
    *   'robots'
        
        Supply the 'content' in the second parameter. If left empty, 'content' will be set to 'noindex' which should
        be set on pages you do not want search engines to index.
        
    *   'dns-prefetch'
        
        Supply a domain name in the second parameter and it will be prefetched so that subsequent requests to it will
        already be resolved.
        
    *   'og'
        
        Supply 'og' for the first parameter, and a keyed array of OpenGraph keys and values, such as:
        
        array(
            'title' => 'My Title',
            'description' => 'Some description...',
            'image' => 'http://some.image/url.png'
        )
        
        This will be converted to individual meta tags such that the first one would be 'og:title' with a content 
        value of 'My Title'.
        
    *   'og:anything'
        
        Supply any OpenGraph key as the first parameter, and the corresponding value as the second parameter. Any
        additional attributes needed can be supplied in the third parameter as a keyed array.
    
    *   'application-name'
        
        Used for pinning your website to the Windows 7 taskbar. This is what the app will be listed as. Supply
        the name of the application as the second parameter.
        
    *   'msapplication-tooltip'
    
        Supply the description for the tooltip as the second parameter.
        
    *   'msapplication-starturl'
        
        Supply the base (starting) URL of your application as the second parameter.
        
    *   'msapplication-task'
    
        Call this for each task you want listed with your application in the taskbar. The second parameter
        should be a keyed array containing 'name', 'action', and 'icon'. 'name' should be the name of the task.
        'action' should be the URL for the task. 'icon' should be the URL of an icon to display next to the task
        name.

*   'earlyMeta' (Default: null)

    This is exactly the same as 'meta', but it meant to be called at the top of <head> for the tags which need to be
    in the first 1024 bytes. To add tags to this field, use the same calls to meta() as described in the 'meta' 
    section abobe, but either (1) add 'early' => true to the third parameter's array, or supply true for the fourth
    parameter. The effect will be the same as normal, except it will end up in 'earlyMeta' instead of 'meta'

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
    
*   'lessLib' (Default: '/auto_asset/js/less-1.2.1.min')

    Indicated the path to the LESS JavaScript library, which will be used for processing of .less files. If you do
    not plan to use .less files, you can set lessLib and lessJs to null so that the files are not loaded.
    
*   'lessJs' (Default: '/auto_asset/js/less')

    Indicates the path to the less.js helper function for the LESS library. It is used when asynchronously loading
    .less files.

Finally, in your AppController's beforeFilter() or beforeRender() callback, add the following line:

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

    echo $this->AssetLoader->required();

You can also pass in a string indicating which type of required file to load ('css', 'less', or 'js') or an 
array of strings. If you wish to use a custom helper when the function calls the script() and css() function,
you can pass one in the second parameter. AutoAsset already uses a custom helper to load .less files, so you
don't need to worry about that. You can load certain types of assets, and then call required() later on to 
output only the types which have out already been output.

Additionally, use the following function near the top of your <head> to output all meta tags defined in your 
controllers:

    echo $this->AssetLoader->meta(true);
    
Then use this function a bit further down to output the rest of your meta tags (still within <head>):

    echo $this->AssetLoader->meta();
    
You can also output a valid <base> tag for your site's base URL with the following function:

    echo $this->AssetLoader->base();
    
You can optionally supply a URL as the first parameter, and if you want it to be self-closing (not required
in HTML5) then pass false for the second parameter.

And finally near the bottom, usually right before the closing body tag, add the following:

    if (isset($assets)) {
        echo $this->AssetLoader->globals();
        echo $this->AssetLoader->load();
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
    $css('libs/jquerytools/overlay');
    $script('libs/jquerytools/overlay', 'overlay');
    
Callbacks do not work properly for stylesheets, so don't bother naming or defining callbacks for your CSS
files at this time. They will load immediately and your content will be styles as soon as they are loaded.

$less
-----

$less works the exact same as $css, except it asynchronously loads .less files instead, using the
LESS Javascript library. Nothing different is needed when calling the files. To load 'app.less' from your
'css' directory, use the following JavaScript:
    
    $less('app');

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
    
Or since you're already using the AssetLoader helper, simply call:

    <?php echo $this->AssetLoader->base(); ?>
    
And a proper <base> tag will be output.

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