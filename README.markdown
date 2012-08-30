AutoAsset Plugin
================
A full-featured and extensible asset management plugin for CakePHP 2.x


What Is It?
===========

*  It's a way to take most or all asset logic out of the layout or view.
*  It's a way to automate certain asset inclusions in true CakePHP fashion
*  It's a way to specify CSS files, JS files, JS global variables, and meta tags from the controller
*  It automates loading CSS/JS files both normally (using the HtmlHelper) and asynchronously


Why?
====

I found myself doing the same things over and over again for every one of my CakePHP projects. I was naming
files the same, including them in the same places in the HTML, and using a lot of the same meta tags every time.

Additionally, I found I often needed to insert a bit of logic to decide whether or not to output a specific asset, 
or to conditionally include one of two possibilities depending on some variable.

Over time, I created a small library of helper functions which became more and more generic until it was eventually
turned into a completely generic asset management plugin known as AutoAsset. It was originally for CakePHP 1.2 but
the current versions support only CakePHP 2.x and recent versions of PHP.


Requirements
============

*  PHP 5.3+
*  CakePHP 2.x (Most recent version developed on CakePHP 2.2.1)


Installation
============

Manual
------

1.   Download the plugin: http://github.com/bmcclure/CakePHP-AutoAsset-Plugin/zipball/master
2.   Unzip the downloaded file to your CakePHP app's 'Plugin' folder
3.   Rename the unzipped folder to 'AutoAsset'


Directly From GitHub
--------------------

1.   Within your project, create the directory app\Plugin\AutoAsset.
2.   From within that directory, run the command:

	git clone https://github.com/bmcclure/CakePHP-AutoAsset-Plugin.git

Note: You can also add the AutoAsset repository as a submodule if your project is under Git version control.


Activating the Plugin
---------------------

No matter how you install the plugin, it only becomes active when you load it from your app's bootstrap.php file.

Some examples of how to load AutoAsset:

First, if loading plugins individually:

    CakePlugin::load('AutoAsset'); //Loads just this plugin

Or, to load all plugins automatically:

    CakePlugin::loadAll(); // Loads all plugins at once


Usage
=====

Placing your media files
------------------------

You can store your media files anywhere within your webroot, but the easiest way to use the plugin is to follow 
the same conventionsused by CakePHP and put JavaScript files under 'app/webroot/js/' and CSS files under 
'app/webroot/css/'. This will require the least configuration, and that's usually preferred.

AutoAsset is able to automatically include CSS and JS files corresponding to the controller and/or action in the
current request. This feature is enabled by default, but nothing will happen unless you either tell AutoAsset
where your controller-based assets are located, or put them in the location AutoAsset is expecting.

By default, place your controller files in a structure such as this:

    /app/webroot/
                 css/controllers/
                                 pages.css
                                 users.css
                                 another_controller.css
                 js/controllers/
                                pages.js
                                users.js
                                another_controller.js

In the above example, both pages.css and pages.js will be loaded when the current request is within 
'PagesController'.

Likewise, you can create CSS and JS files for individual actions to further separate logic and keep your 
files sparse and clean. Create a structure such as this:

    /app/webroot/
                 css/controllers/
                                 pages/
                                       display.css
                                 users/
                                       add.css
                                       index.css
                 js/controllers/
                                users/
                                      index.js
                                      delete.js

In the above example, when PagesController is displaying a page, pages.css will be loaded. When on the main
Users index, users/index.css and users/index.js will be loaded.

You can combine both controller and action files. In this case, with both of the above examples, when on 
Users index the following files will be loaded if they exist:

    css/controllers/users.css
    css/controllers/users/index.css
    js/controllers/users.js
    js/controllers/users/index.js

You don't need to create a controller file to be able to use an action file. If you do use both,
the controller file will start loading before the action file, but by default they are loaded 
asynchronously and thus are not guaranteed to finish loading in the same order. Keep reading to 
learn the recommended way to define dependencies for your JS and CSS files when using AutoAsset.

For the rest of your JS and CSS assets, place them anywhere in a logical structure underneath the respective
app/webroot/js/ and app/webroot/css/ directories. In this way you can load them with AutoAsset, or load them
with some other tool, or Cake's core HtmlHelper, without moving them.

Some simple file placement recommendations:

*  Place JS libraries under js/libs/; If it's a multi-file library, create a directory for it under js/libs/
*  Place Jquery plugins under js/libs/jquery/ or js/libs/jquery/plugins/ to keep them better secluded
*  Place CSS files under css/libs/ with the same guidelines as for JS files


Loading the component
---------------------

Next, load the AssetCollector component in your AppController. Your $components array might look like this:

    public $components = array('AutoAsset.AssetCollector');

Or maybe you want to provide a couple of options to AssetCollector:

    public $components = array(
        'AutoAsset.AssetGatherer' => array(
            'asyncJs' => 'app',
            'requiredJs' => array('libs/modernizr-1.7-custom.min', 'libs/selectivizr'),
        ),
    );

The full set of options you can provide to the component (and their defaults) are:

*   'assets'

    A default set of assets to include for all controllers and actions. For large or complex groups of assets,
    this can also be set within beforeFilter, where all assets can be added together or individually.

    The format for specifying them here is:

    'assets' => array(
        'headTop' => array(
            'js' => 'bootstrap',
            'css' => array(
                'style',
                'someFile' => array('option' => 'value'),
            ),
        ),
    ),

    The first level of the array is the name of an asset block. If it isn't already defined it will be created with
    default settings.

    Underneath the block name is a keyed array of asset types to assets. In this case we're specifying 'js' and 'css'
    assets. The array value can either be a string for a single asset, or an array for multiple assets.

    If specifying multiple assets for a particular asset type, you can optionally specify settings for each asset as
    another nested keyed array as demonstrated with 'someFile' above.

    You can also specify other asset types to include here, such as 'jsGlobal' or 'meta'.

*   'blocks'

    An associative array of block names and options in the form:

    'blocks' => array(
        'name' => array(
            'option' => 'value',
            'option' => 'value',
        ),
    ),

    Any option not provided will be populated from the 'blockDefaults' array (see below).

    The default blocks that will be included if not overridden are: 'headTop', 'head', 'headBottom', 
    'bodyTop', 'body', and 'bodyBottom'. The only non-default option used by the blocks is within 
    'bodyBottom' which uses the 'async' renderer instead of 'default'.

*   'blockDefaults'

    An associative array of default options for each asset block if not overridden. If not specified
    the defaults will be:

    'renderer' => 'default',
    'ignoreTypes' => array('ajax'),
    'conditional' => array(),

*   'jsHelpers'

    An associative array of names and paths for each of the JavaScript helpers that AutoAsset should load.
    By default these are:

    'script' => '/auto_asset/js/script.min',
    'css' => '/auto_asset/js/css',
    'namespace' => '/auto_asset/js/namespace',
    'url' => '/auto_asset/js/url',

*   'controllerAssets'

    An associative array defining the types of assets to auto-load based on controller and/or action.
    By default these include:

    'css' => TRUE,
    'js' => TRUE,

*   'jsHelpersBlock'

    The name of the asset block that AutoAsset's JS helpers should be output within. By default 
    this is 'headTop'.

*   'controllersBlock'

    The name of the asset block in which to output the auto-included controller and action files. By
    default this is 'head'.

*   'controllersPath'

    The path under each asset type's main directory in which controller and action files can be found.
    By default this is simply 'controllers' which corresponds to /js/controllers/ and /css/controllers/.

*   'assetsVar'

    The name of the variable that will be included in the view containing all assets. The main reason
    this should ever be changed is if you are using the variable name $assets for something else in your
    layout or views.

    If you change this here, make sure to also set the same option for AssetRendererHelper so it knows
    where to look.


Using the component
---------------------

You can do more with the AssetCollectorComponent than just configure it when including. In fact, it's often
easier to specify your assets from beforeFilter() in AppController rather than stuffing them all into the
component's configuration array.

The available methods are:

*   jsGlobal($name, $value = '', $block = 'headTop')

    Output a global JavaScript variable that you can access from any other scripts on the site. Be careful
    not to use a name that will override something else, or be overridden by something else, or unexpected
    results can occur.

    You can provide multiple globals with a single function call in one of two ways:

    jsGlobal(array('key1', 'key2', 'key3'), array('value1', 'value2', 'value3'));

    Or:

    jsGlobal(array('key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3')));

*   js($path, $block = 'bodyBottom')

    Output a JavaScript file indicated by $path (relative to the asset type dir, or a full URL) in the indicated asset block.

    $path can either be a string for a single file, or an array of strings to specify multiple files to load.

*   css($path, $rel = 'stylesheet', $media = 'screen', $block = 'head')

    Output a CSS file indicated by $path as type $rel for media type $media within asset block $block.

    $path can be a string for a single file, or an array of strings for specifying multiple files in one call.

*   meta($type, $url = NULL, $options = array(), $block = 'head')

    Output any meta tag within the head.

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

*   block($name, $settings = array())

    Defines a new (or replacement) asset block from within a controller.

    You only need to call this method if (a) You didn't define the block within your $components array and (b) You
    want to use custom settings other than the defaults.

    If you don't need any custom settings, then this exact same behavior will happen automatically the first time you
    try to put an asset in the block, so you don't need to call this function at all.

    Often, you may want to create a block using the 'async' renderer (or some other custom renderer), in which case calling
    this method to define the custom renderer is appropriate.

    Example:

        $this->AssetCollector->block('app', array('renderer' => 'async'));

    The above code would specify a new block named 'app' that will render assets asynchronously where appropriate.

*   resetControllersPath($path)

    If you want to change the path to controller/action files after loading the component, use this method 
    to ensure all internals are updated. Just pass a relative path underneath each asset type's directory where
    the files can be found. To emulate the default functionality you'd call resetControllersPath('controllers').


Loading the helper
------------------

Also in your AppController, add the AssetRenderer helper to your $helpers array. It might look like this:

    public $helpers = array('AutoAsset.AssetRenderer');

You can also provide a few settings to AssetLoader to customize its behavior:

*   'assetsVar'

    Defaults to 'assets'. It should match what you've set for the 'assetsVar' setting in the AssetCollector component. If you
    haven't customized it there, then don't customize it here.

*   'helpers'

    The names of helpers that should be made available to AssetRenderer objects. The defaults:
    
        array('Html', 'AutoAsset.AsyncAsset')

*   'assetTypes'

    The available types of assets to output. The defaults:

        array('js', 'css', 'jsGlobal', 'metaTag')

*   'asyncTypes'

    The types of assets that can be handled asynchronously. The defaults:

        array('js', 'css')


Configuring your layout
-----------------------

Within your layout (or views), you should output each block where appropriate. The default blocks included with
AutoAsset are 'headTop', 'head', 'headBottom', 'bodyTop', 'body', and 'bodyBottom'. If you make use of any of
these blocks, make sure you output them in your layout.

Use the following method to render a block:

    $this->AssetRenderer->render('headTop');

Each block has its own settings internally for how it should be rendered, so you don't need to worry about that here. By default,
JavaScript files added to the 'head' block are rendered normally, while JavaScript files added to 'bodyBottom' will be rendered 
asynchronously. Either way, you simply call renderBlock() with the name of the block to render.

You can also explicitely render an AssetBlock, AssetCollection, or an individual Asset. Examples:

    // @var AssetBlock $block
    $this->AssetRenderer->renderBlock($block);
    
    // @var AssetCollection $collection
    $this->AssetRenderer->renderCollection($collection);
    
    // @var Asset $asset
    $this->AssetRenderer->renderAsset($asset);


Using custom renderers
----------------------

You can replace or define custom renderers that blocks should use from within your layout if necessary.

An example:

    $this->AssetRenderer->setRenderer('custom', new CustomAssetRenderer());

Now, any blocks defined with the 'custom' asset renderer will properly use your defined renderer.

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


Outputting semantic markup
==========================

Many common patterns are repeated throughout layouts. While it may eventually be extracted to its own plugin,
AutoAsset currently also includes the SemanticMarkupHelper that has a few methods to simplify using current standards.

Load it in your $helpers array, such as:

    public $helpers = array('AutoAsset.AssetRenderer', AutoAsset.SemanticMarkup');

Then, you can use the following methods to output some useful tags automatically:

*   conditionalHtmlTag($class = "no-js")

    This will output an opening HTML tag using a common pattern with IE conditional comments. With the default
    parameter, the following would be output:

        <!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
        <!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
        <!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
        <!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->

*   endConditionalHtmlTag()

    Generally, you should simply put a closing HTML tag in your layout manually. Sometimes, this will cause errors
    to be reported in your IDE since the conditionalHtmlTag() method isn't seen as an opening tag. If there is no 
    other way around it, you can call this method to output a simple closing HTML tag.

*   base($url = NULL, $html5 = TRUE)

    Outputs a proper base tag. Call the method in the head of your layout. Without any parameters, it will use the base path
    of your CakePHP application.

    The $html5 parameter defines how the tag should look. If true, the tag will be HTML5-compliant and will not be self-closing.
    If false, the tag will be XHTML-compliant and will self-close.

*   chromeFrameBar($message = null, $minSupportedIE = "8")

    Outputs a message about upgrading the browser or using Chrome Frame. Uses an IE conditional comment to only appear if
    less than the specified IE version.

    By default, the message appears for IE7 and earlier (since these browsers are no longer supported by Microsoft as of 
    2012).

    With the default parameters, the following will be output:

        <!--[if lt IE 8]>
            <p class=chromeframe>Your browser is <em>unsupported</em>. <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p>
        <![endif]-->

Final Notes
===========

More to come!