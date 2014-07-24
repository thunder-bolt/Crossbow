<?php
/**
 * Created by PhpStorm.
 * User: Kolier
 * Date: 7/19/14
 * Time: 12:52 AM
 */

namespace Crossbow\Controllers;

use Silex;

class Template {

    public static function record(Silex\Application $app, $contenttypeslug, $slug, $template)
    {

        $contenttype = $app['storage']->getContentType($contenttypeslug);

        $slug = makeSlug($slug, -1);

        // First, try to get it by slug.
        $content = $app['storage']->getContent($contenttype['slug'], array('slug' => $slug, 'returnsingle' => true));

        if (!$content && is_numeric($slug)) {
            // And otherwise try getting it by ID
            $content = $app['storage']->getContent($contenttype['slug'], array('id' => $slug, 'returnsingle' => true));
        }

        // No content, no page!
        if (!$content) {
            // There's one special edge-case we check for: if the request is for the backend, without trailing
            // slash and it is intercepted by custom routing, we forward the client to that location.
            if ($slug == trim($app['config']->get('general/branding/path'), "/")) {
                simpleredirect($app['config']->get('general/branding/path') . "/");
            }
            $app->abort(404, "Page $contenttypeslug/$slug not found.");
        }

        // Then, select which template to use, based on our 'cascading templates rules'
        if (empty($template)) {
            $template = $content->template();
        }

        // Fallback: If file is not OK, show an error page
        $filename = $app['paths']['themepath'] . "/" . $template;
        if (!file_exists($filename) || !is_readable($filename)) {
            $error = sprintf(
                "No template for '%s' defined. Tried to use '%s/%s'.",
                $content->getTitle(),
                basename($app['config']->get('general/theme')),
                $template
            );
            $app['log']->setValue('templateerror', $error);
            $app->abort(404, $error);
        }

        // Setting the canonical path and the editlink.
        $app['canonicalpath'] = $content->link();
        $app['paths'] = getPaths($app);
        $app['editlink'] = path('editcontent', array('contenttypeslug' => $contenttype['slug'], 'id' => $content->id));
        $app['edittitle'] = $content->getTitle();

        // Make sure we can also access it as {{ page.title }} for pages, etc. We set these in the global scope,
        // So that they're also available in menu's and templates rendered by extensions.
        $app['twig']->addGlobal('record', $content);
        $app['twig']->addGlobal($contenttype['singular_slug'], $content);

        // Render the template and return.
        return $app['render']->render($template);
    }

}
