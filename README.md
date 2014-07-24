Crossbow for Bolt
=================

App functions for Bolt.

URL Redirect
------------

- Add fields to contenttype.
    - redirect_url
    - redirect_status
    - redirect_counter

        redirect_url:
            type: text
            pattern: url
        redirect_status:
            type: integer
            default: 302
        redirect_counter:
            type: integer
            default: 0

- Add routes to routing.
    - record
    - redirect

        link_record:
            path: /link/{slug}
            defaults: { _controller: 'Bolt\Controllers\Frontend::record', 'contenttypeslug': 'link' }
            contenttype: links

        link_redirect:
          path: /link/{slug}/goto
          defaults: { _controller: 'Crossbow\Controllers\Redirect::redirect', 'contenttypeslug': 'link' }
          contenttype: links


URL Redirects
-------------

- Do as URL Redirect.
- Add fields to contenttype.
- Add routes to routing.
