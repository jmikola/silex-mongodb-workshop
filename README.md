# Silex and MongoDB Workshop

This repository contains an example Silex application, which was used for the
*Building Your First App with Silex and MongoDB* workshop. Abstract follows:

> This session will introduce Silex, a PHP micro-frame work based on Symfony2
components, and MongoDB, a non-relational, schema-free document database, as we
combine them to build a geo-enabled web application. We’ll explore Silex's
routing and controller architecture to start, and then dive into MongoDB's
document model, query language, and geospatial features once we start
implementing data persistence. Additionally, we will define services in Silex
(even micro-frameworks benefit from thin controllers!), take advantage of Twig
for templating, and discuss project deployment for both MongoDB and our
application.

## Installation

Clone this repository and install/update dependencies with Composer:

```
$ git clone git://github.com/jmikola/silex-mongodb-workshop.git
$ cd silex-mongodb-workshop
$ composer update
```

The application also requires that you have MongoDB server and the MongoDB PHP
driver installed. Additional resources include:

 * [MongoDB server installation](http://docs.mongodb.org/manual/installation/)
 * [MongoDB PHP driver installation](http://php.net/manual/en/mongo.installation.php)

### Configuration

Customize the application configurations as needed. The repository includes
`config/dev.php.dist` and `config/prod.php.dist` files, which may be customized
by copying each to a new file without a `.dist` extension and modifying as
necessary. Configuration files without `.dist` are ignored by Git, which makes
them suitable to hold private data (e.g. API keys).

The prod configuration file contains parameters for Twitter OAuth (empty by
default) and the MongoDB database name (`silex` by default). The dev config
inherits prod, and generally needs no customization.

### Importing Data fixtures

Venue data fixtures are included in the repository as `venues.json`. These must
be imported into the appropriate MongoDB collection (`silex.venues` by default):

```
$ mongoimport -d silex -c venues --upsert < venues.json
```

## Running the Application

The easiest way to run this application is using the built-in web-server found
in PHP 5.4+. The `serve.sh` script in the repository can be used to launch this.

Additionally, the Silex documentation discusses various
[web server configurations](http://silex.sensiolabs.org/doc/web_servers.html) if
you would like a more permanent solution.

## Relevant Resources

The `workshop.pdf` file included in the project repository serves as an outline
for topics covered during the workshop. Additionally, it contains links to many
subjects not covered in the slides below, such as importing data from Foursquare
and OpenStreetMap.

The *Getting Acquainted with MongoDB* presentation is available on
[GitHub](http://jmikola.github.io/slides/mongodb_getting_acquainted). An older
version of those slides is also available on
[Speaker Deck](https://speakerdeck.com/jmikola/getting-acquainted-with-mongodb).

Dustin Whittle's *Silex: From Micro to Full-stack* presentation from Symfony
Live London 2013 is available on both
[YouTube](https://www.youtube.com/watch?v=6U6RmtHxV9g) and
[Speaker Deck](https://speakerdeck.com/dustinwhittle/silex-from-micro-to-full-stack-1).

Additional references for this workshop include:

 * [MongoDB PHP driver documentation](http://php.net/manual/en/book.mongo.php)
 * [MongoDB server documentation](http://docs.mongodb.org/manual/)
 * [Silex documentation](http://silex.sensiolabs.org/documentation)
 * [Twig documentation](http://twig.sensiolabs.org/documentation)
 * [GeoJSON specification](http://geojson.org/)
