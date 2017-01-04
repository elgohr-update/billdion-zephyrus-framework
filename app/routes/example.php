<?php

/**
 * Simple example showcasing the basic usage of direct route definition instead
 * of using routable controllers. Every php files under the /routes directory
 * are automatically loaded. The $router variable is directly available in these
 * files.
 *
 * The /routes directory can be removed completely if you are only using
 * controllers.
 */
$router->get("/example/static", function() {
    echo "it works !";
});

/**
 * Route's arguments can be directly passed to the callback function of they can
 * simply be accessed using Request::getParameter() method exactly as in a
 * routable controller.
 */
$router->get("/example/static/{id}", function($id) {
    echo "it works with id $id !";
});