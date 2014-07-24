<?php
/**
 * Created by PhpStorm.
 * User: Kolier
 * Date: 6/25/14
 * Time: 5:39 AM
 */

namespace Crossbow\Controllers;

use Silex;
use Bolt\Controllers\Frontend;

/**
 * URL Redirect Actions
 */
class Redirect
{

    public static function redirect(Silex\Application $app, $contenttypeslug, $slug)
    {
        $raw = array(
            'app' => $app,
            'contenttypeslug' => $contenttypeslug,
            'slug' => $slug,
        );

        $contenttype = $app['storage']->getContentType($contenttypeslug);

        $slug = makeSlug($slug, -1);

        // First, try to get it by slug.
        $content = $app['storage']->getContent($contenttype['slug'], array('slug' => $slug, 'returnsingle' => true));

        if (!$content && is_numeric($slug)) {
            // And otherwise try getting it by ID
            $content = $app['storage']->getContent($contenttype['slug'], array('id' => $slug, 'returnsingle' => true));
        }

        $url = $content->get('redirect_url');
        if (!$content || !$url) {
            return Frontend::record($raw['app'], $raw['contenttypeslug'], $raw['slug']);
        }

        $status = $content->get('redirect_status');
        $counter = $content->get('redirect_counter');

        // Add counter
        $content->setValue('redirect_counter', $counter + 1);
        $app['storage']->saveContent($content);

        return $app->redirect($url, $status);

        return Frontend::record($raw['app'], $raw['contenttypeslug'], $raw['slug']);
    }

    public static function redirects(Silex\Application $app, $contenttypeslug, $slug, $sub)
    {
        $raw = array(
            'app' => $app,
            'contenttypeslug' => $contenttypeslug,
            'slug' => $slug,
        );

        $contenttype = $app['storage']->getContentType($contenttypeslug);

        $slug = makeSlug($slug, -1);

        // First, try to get it by slug.
        $content = $app['storage']->getContent($contenttype['slug'], array('slug' => $slug, 'returnsingle' => true));

        if (!$content && is_numeric($slug)) {
            // And otherwise try getting it by ID
            $content = $app['storage']->getContent($contenttype['slug'], array('id' => $slug, 'returnsingle' => true));
        }

        $redirects = $content->get('redirects');
        if (!$content || !$redirects) {
            return self::redirect($raw['app'], $raw['contenttypeslug'], $raw['slug']);
        }
        $redirects_counter = unserialize($content->get('redirects_counter'));
        if (empty($redirects_counter)) {
            $redirects_counter = array();
        }

        $lines = explode("\n", $redirects);
        foreach ($lines as $line) {
            list($name, $url, $status) = explode('|', $line);
            if (!$status) {
                $status = 302;
            }
            // Try to match the request name.
            if ($sub == $name) {
                // @TODO Issue in Content class.
                if (!isset($redirects_counter[$sub])) {
                    $redirects_counter[$sub] = 0;
                }
                $redirects_counter[$sub]++;
                $content->setValue('redirects_counter', serialize($redirects_counter));
                $app['storage']->saveContent($content);
                //return $app->redirect($url, $status);
            }
        }

        return self::redirect($raw['app'], $raw['contenttypeslug'], $raw['slug']);
    }

}
