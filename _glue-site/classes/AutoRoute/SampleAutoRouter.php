<?php
/**
  * Glue Framework
  * Copyright (C) 2015 Joby Elliott
  *
  * This file is not licensed along with the rest of the Glue Framework. It is
  * released into the public domain, and you are free to modify or re-use it
  * in any way.
 */
namespace AutoRoute;
use \glue\Template;

class SampleAutoRouter {
  public function route() {
    Template::set('pageTitle','Sample AutoRoute');
    echo "<p>This is a page produced by \AutoRoute\SampleAutoRouter::route()</p>";
  }
}
