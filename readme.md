# PMG Core

PMG Core is a collection of utilities that PMG uses on its own and its clients
sites.  It's a library, with a tiny bit of functionality baked in.

**Note: this plugin requires PHP 5.3+**

Some things it does:

* Seeks to automate the creation of admin area fields.
* Makes adding meta boxes, user profile fields, and term fields really easy
* Automates the creation of post types and taxonomies.

## Whirlwind Tour

The central entry point is the `pmgcore` function.  You use this to create your
own "projects".

    <?php
    $p = pmgcore('my_project');

You can call `pmgcore` anytime after `plugins_loaded` fires. Calling it multiple
times with the same `$key` will not create new projects or overwrite a project
that was already created.

### Adding Types

    <?php
    pmgcore('my_project')->create_type(
        'the_post_type',
        __('Singular Name', 'your-textdomain'),
        __('Plural Names', 'your-textdomain'),
        array(
            'public'             => true,
            'show_in_nav_menues' => false,
        )
    );

### Adding Taxonomies

    <?php
    pmgcore('my_project')->create_taxonomy(
        'the_taxonomy',
        __('Singular Name', 'your-textdomain'),
        __('Plural Names', 'your-textdomain'),
        array(
            'show_ui'   => true,
        ),
        array('page') // post type you want (optional, defaults to post)
    );

### Adding Settings (and Other) Fields

Adding settings fields/sections to already existing pages (General Options
here).

    <?php
    $f = pmgcore('my_project')->setting('my_setting', 'general');

    $f->add_field('field_key, array(
        'label'     => __('Some Label', 'your-textdomain'),
        'type'      => 'text_input', // this is the default
        'cleaners'  => array('esc_url_raw'), // array of callable to run the field through on validation
        'section'   => 'default', // what section this belongs in (optional)
    ));

    // add a new section
    $f->add_section('section_key', array(
        'title'     => __('Section Title', 'your-textdomain'),
        'help'      => __('Section help text', 'your-textdomain'),
    ));

    // put fields in the new section
    $f->add_field('another_field_key', array(
        'label'     => __('Another Field', 'your-textdomain'),
        'type'      => 'textarea',
    ));

You can also add fields to a custom page by calling `pmgcore()->setting` without
the second argument.

All field creation has the same API: `add_section` and `add_field`. As you want
to create fields for a meta box:

    <?php
    $f = pmgcore('my_project')->box_fields('my_metabox');

    // use $f as above

Or to create fields that use any of WordPress' various meta tables.

    <?php
    $f = pmgcore('my_project')->meta_fields('my_metafields');

    // use $f as above

Additionally all fields have a `render` method which spits out the fields
themselves.  This behaves differently depending the type of fields created. See
[PMG\Core\Fields](https://github.com/AgencyPMG/PMG-WP-Core/tree/master/inc/Fields)
for more information.

### Creating Admin Pages.

First step: create fields like above.

    <?php
    $f = pmgcore('my_project')->settings('my_setting');

    // do stuff with $f

Then you can use the `admin_page` method.

    <?php
    pmgcore('my_project')->admin_page('page_key', $f, array(
        'title'     => __('Page Title', 'your-textdomain'),
        'menu_name' => __('Menu Name', 'your-textdomain'),
        'parent'    => 'options-general.php', // optional -- default is none, a top level menu page
        'slug'      => 'your-page-slug',
    ));

### Adding Meta Boxes

Create a `MetaBoxFields` object like above.

    <?php
    $f = pmgcore('my_project')->box_fields('my_metabox');

    // do stuff with $f

The use the `meta_box` method.

    <?php
    pmgcore('my_project')->meta_box('box_key', $f, array(
        'title'     => __('Box Title', 'your-textdomain'),
        'priority'  => 'high', // optional, default is 'high'
        'context'   => 'normal', // optional, default is 'normal'
    ));

The above will add a meta box to all public post types. You can specify post
types with an optional last argument:

    <?php
    // put the box on pages.
    pmgcore('my_project')->meta_box('box_key', $f, array(
        'title'     => __('Box Title', 'your-textdomain'),
        'priority'  => 'high', // optional, default is 'high'
        'context'   => 'normal', // optional, default is 'normal'
    ), array('page'));

### Adding User & Term Fields

Like pretty much everything else: create a fields object (using `meta_fields`).

    <?php
    $f = pmgcore('my_project')->meta_fields('myterm_fields');

    // do stuff with $f

To add user fields use the `user_box` method.

    <?php
    pmgcore('my_project')->user_box('box_key', $f);

Or to put fields on user pages, use the `term_box` method.

    <?php
    pmgcore('my_project')->term_box('box_key', $f);

The above will put fields on all taxonomies with `show_ui` set to `true`. To
specify taxonomies, use the optional last argument.

    <?php
    pmgcore('my_project')->term_box('box_key', $f, array('category'));

### Meta Objects

PMG Core contains some wrappers of the WordPress metadata API to make things
easier to fetch and save.  Namely, the library will prefix things for you so you
don't have to worry about naming collisions.

There are four properties that contain these wrappers: `postmeta`, `usermeta`,
`commentmeta`, `termmeta`.

They all have the same API:

    <?php
    $m = pmgcore('my_project')->postmeta;

    // put 'a value' in with the key '_my_project_some_key'
    $m->save($some_post_id, 'some_key', 'a value');

    // fetch the value in 'some_key'
    $m->get($some_post_id, 'some_key', 'default value');

    // delete a key
    $m->delete($some_post_id, 'some_key');

    // delete all values with the key 'some_key'
    $m->delete_all($some_post_id, 'some_key');

`termmeta` is not really termmeta.  It fakes term meta using the options table.
However, if `$wpdb->termmeta` is set (eg. someone has added a termmeta table) it
will use that.

The limitation here is that the library deals with only single meta items. This
may change in the future.

## Adding Rewrites

Use the `PMG\Core\Project::$router` property.

### Adding a rewrite rule

    <?php
    pmgcore('my_project')->router->add_rule(
        '^some-route/(\d+)/?$', // just regex
        'index.php?some_var=$matches[1]',
        'top' // this is option, defaults to top
    );

### Adding Rewrite Endpoints

    <?php
    pmgcore('my_project')->router->add_endpoint('ep', EP_ALL);

The second argument is option, defaults to `EP_ALL`.  Learn more about
endpoints
[here](http://make.wordpress.org/plugins/2012/06/07/rewrite-endpoints-api/).

### Adding Query Vars

    <?php
    pmgcore('my_project')->router->add_var('some_var');

    // add more than one
    pmgcore('your_project')->router->add_var(array('some_var', 'some_other_var'));

### Using the Router

The above doesn't gain you much more than a bit of convenience.  Use the
`add_route` property to take some shortcuts.

`add_route` only takes one argument: a route.  The route is just a string with
several variable built in.  The variables, in this case, take the form of
`<(int|str):some_key>`.  `add_route` will translate those into a rewrite.

So this:

    <?php
    pmcore('my_project')->router->add_route('route/<int:some_var>/<str:other_var>');

Is a shortcut for this:

    <?php
    $r = pmgcore('my_project')->router;

    // add a rule
    $r->add_rule(
        '^route/(\d+)/([^/]+)/?$',
        'index.php?some_var=$matches[1]&other_var=$matches[2]'
    );

    // add the query vars
    $r->add_var(array('some_var', 'other_var'));

The downside, of course, is less fined grained control.  If you need any sort of
complete regex for your rewrite rules, it's better to just use strait regex and
`add_rule`.

### "Catching" Query Variables.

Sometimes you want to "catch" query variables on the front end and do certain
things if they hapen to be set.  `PMG\Core\Router::catch_var` let's you do that.

It takes two arguments: a query var to search and the callable to call when it's
found.  There's an optional third argument, `$exit`, which, if true, will cause
the execution to stop after the callable has been called. `$exit` defaults to
`true`.

    <?php
    pmgcore('my_project')->router->catch_var('some_var', function($v) {
        echo $v; // $v is the query var that was caught
    });

## Pluggable

PMG Core uses dependency injection to prevent loading a bunch of crap you don't
need.  In short, you can use the `pmgcore` entry point and only objects that you
use explicitely will be created.

To give an example, the first time you use the `postmeta` property, the object
that wraps the metadata API for post meta is created.

You can also mix and match classes as you see fit. I tried to keep everything
loosely coupled.

## Functionality

There is a little bit of
[functionality](https://github.com/AgencyPMG/PMG-WP-Core/tree/master/inc/Functionality) 
baked into this plugin.

### Cleanup

* Remove the meta generator tag from the `<head>` section
* Allow users how can post `unfiltered_html` to put whatever they like in term
descriptions
* Set the default pingback flag to off
* Set the default ping status to off
* Set the default comment status to off
* Enable comment moderation
* Disable XML RPC
* Disabled WP-App (for WordPress 3.4 and lower)
* Remove all but the "Right Now" dashboard meta boxes

### Uploads

* Set the upload path to `{$_SERVER['DOCUMENT_ROOT']}/uploads`
* Set the upload url to `//{WP_HOME}/uploads`

### Headers

* Remove the shortlink header
* Add `X-Frame-Options: SAMEORIGIN` as a header on all WP rendered pages.
* Add `X-UA-Compatible: IE=edge,chrome=1` as a header on all WP rendered pages.
* Remove the `X-Pingback` header.
* Set an `X-Powered-By` header

### Enqueues

A single CSS and JS enqueue for the admin area -- this to make some pretty tabs
on metaboxes with multiple field sections.
