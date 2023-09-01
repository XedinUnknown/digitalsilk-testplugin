# DigitalSilk - Test Plugin
[![Continuous Integration](https://github.com/xedinunknown/digitalsilk-testplugin/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/xedinunknown/digitalsilk-testplugin/actions/workflows/continuous-integration.yml)
[![Latest Stable Version](https://poser.pugx.org/xedinunknown/digitalsilk-testplugin/v)](//packagist.org/packages/xedinunknown/digitalsilk-testplugin)
[![Latest Unstable Version](https://poser.pugx.org/xedinunknown/digitalsilk-testplugin/v/unstable)](//packagist.org/packages/xedinunknown/digitalsilk-testplugin)

A WordPress plugin that imports products from DummyJSON into WooCommerce.
Intended to pass a DigitalSilk test.

## Requirements
- PHP >= 7.4;
- WordPress >= 6.1;
- WooCommerce >= 7.9;

## Setup From Source
0. Ensure you have a working Docker setup with Docker Compose.
1. Obtain contents of the repo.
2. Follow basic [setup instructions][`wp-oop/plugin-boilerplate`]: 
   1. Copy `.env.example` to `.env`. Usually, defaults work well.
   2. Build environment: `docker compose build`.
   3. Spin it up with `docker compose up wp_dev`, ensuring the procedure is complete:
      the DB, webserver, WP, etc. should be automatically set up.
3. Visit the [local site](http://localhost), and ensure it is ready (WC setup, etc).
    The admin credentials are `admin`/`admin`.
4. To test a distributable version of the plugin, build it by running
   `docker compose run --rm build make release`. Find the dist archive in `build/release`.

## Usage
All configuration is done on the _WooCommerce > DummyJSON_ page in the admin.
In order to save it, press the "Save" button at the bottom. This will override any defaults
by assigning explicit values.

### API
In order to access the remote API's endpoints that require authentication,
the _Username_ and _Password_ settings must be configured with respective [user credentials][api-users].

### Import
Press the _Import Now!_ button to schedule an import for executing ASAP in the background.

> **Important**: The speed and amount of products that can be imported in one batch
> greatly depends on the resources available on the server. For this reason, each product
> has a certain amount of time to be imported, after which the execution timeout resets.
> Theoretically, this should allow any number of products to be imported in one batch.
> 
> This is independent of the execution timeout of the rest of the script, but requires
> PHP to be able to set INI values with `ini_set()`

During import, the plugin will access the products endpoint of the API, and attempt to use its data
to create new products. The specification explicitly says to leave out the `id` field while importing,
and this prevents synchronisation of existing products: on every import, each product will be created anew.
This includes images, which are downloaded and registered in the Media Library.

All importing happens on a separate server thread via WP Cron. During import, if the API
reports that there are more products that have been processed, the process will schedule itself
to continue importing the next batch. This helps avoid timeouts.

The response of the API is streamed. During import, product data is read item by item from the response,
each one is converted to a model on the fly, and given to the importer. This means that there at most
a few kilobytes of memory are used per product entry from the API, and the response is never loaded in its entirety.
Given sufficient time, this means that a very large dataset can potentially be imported without running out of memory.

- The _Batch Size_ setting controls the max amount of products to process **per batch**.
    When this number is reached, another batch will be scheduled, and will continue where the previous one stopped.
- The _Import Limit_ setting controls the total number of items that can be processed **per import**.
    When this number is reached, import concludes, and no more batches are scheduled.
    This is very useful for testing, because it helps avoid importing a large number of items.

Together, these settings allow for fine control over import scope and resources used.

### Troubleshooting
Because the plugin already requires WooCommerce, it integrates with its [logging system][wc-logs].
During import, it will generate entries in the `digitalsilk-wc-import` log.
Entries will be generated for every import, for every batch, and for every product.
This provides helpful information on the progress, and can help determine whether importing has finished,
or encountered a problem.

## Notes
- This package was created from [`wp-oop/plugin-boilerplate`][]. Please refer to its documentation for
    startup instructions and other related technical information.
- This project uses a modular system. It is therefore a demonstration of how application concerns
    may be separated, and interact in explicit ways. Please refer to "[Cross-Platform Modularity in PHP][]"
    for a detailed explanation of approach and features.
- It took a great deal of effort to read product JSON from the API in a streaming way.
- There are many checks in place that ensure a high level of [Engineering Excellence].
    That includes automated tests and static analysis. See build logs for more details.
- The dist build procedure allows this plugin to be easily tested on a separate site.
    I used [InstaWP][] to test in a pristine and separate environment, and it worked on the first try.


[`wp-oop/plugin-boilerplate`]: https://github.com/wp-oop/plugin-boilerplate/
[Cross-Platform Modularity in PHP]: https://dev.to/xedinunknown/cross-platform-modularity-in-php-30bo
[Engineering Excellence]: https://solutionshub.epam.com/blog/post/engineering-excellence-in-software-development
[InstaWP]: https://instawp.com/
[api-users]: https://dummyjson.com/users
[wc-logs]: https://woocommerce.com/document/finding-php-error-logs/
