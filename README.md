A simple middleware for Slim4 framework that add GZIP compression to the output. Usage is very simple, just enable this Middleware (in the Middleware part of your Slim4 Project) with:
```
<?php

use PerSeo\Middleware\GZIP\GZIP;

$app->add(GZIP::class);
```
After this, your Slim 4 project returns a GZIP compressed page if browser support it.
