# Overall Flow

If kx was originally a knockoff of Drupal, now it's essentially a knockoff of Slim.

However, calling it a knockoff isn't quite right. Ultimately, the goal here is that the Edaha software consists of two parts:

1. The framework itself, known as "kx"
2. The Business Logic of a *Board Software, known as "Edaha"

PHP has come a long, long way from when kusaba nee trevorchan was first created. The PHP Framework Interop Group (PHP-FIG) now exists and publishes PHP Standards Recommendations that make PHP development look a lot different than the Wild Wild West of the '00s. PHP itself has continued to evolve and become safer and easier to write in. It's a great time to be making new software.

# The kx Framework

The kx Framework is our custom framework for the web application. "Custom framework" may make a lot of people shudder, because why do we need Yet Another One?

Well, really, we don't. We could just use something off-the-shelf and build Edaha within that and ultimately end up in the same place. But Edaha, and the kx framework, are a bit of a love-letter to the internet of old. We are doing this because we can, because the internet lets us. We make *boards because we can, because the internet lets us. The internet is an amazing thing, despite its current state. Also, it's a learning experience. It's fun to learn.

The kx framework is ultimately tailored for *board software, but should be flexible enough that one could theoretically create any type of web application in it.

The expected lifecycle for a kx Application is very similar to Slim:

1. Instantiation and configuration of the Application
2. Fallback route definitions
3. Application execution

The kx Framework will provide some standard middleware:

1. Twig templating
2. Error handling
3. Response caching
4. Session management

## kx Applications

kx Applications implement specific routes and their behaviors. Edaha is a kx Application.

# Edaha

Edaha is social media software. Before the internet consolidated into the Big 3 (Meta, TikTok, Reddit), social spaces on the internet consisted of games and forums. Forums came in all niches, genres, and interests. Forums came in all sorts of variants, evolving from the BBSes of yore. Somewhere inbetween came textboards and imageboards.

Edaha (枝葉) means "branches and leaves", which is used as inspiration for how Edaha should operation. The Github's description, written many years ago, reads:

> Edaha is a modular, object oriented image BBS software written in PHP, 
> written using the custom Kx framework and loosely based on Kusaba X.
> It is designed to be modular (any part of the code can be extended with 
> modules) and easy to set up and use.

## Capabilities

By default, Edaha will have the following capabilities:

1. An index page for the site
2. Support for Boards, with initial support for variants Textboard and Imageboard
3. Support for posting, including pre- and post-hooks for posting
3. Site Management and Post Moderation
4. A plugin system for the above, so it can be extended easily in the future.

## Deployment

The only things that should need to be configured prior to deploying Edaha to production are the connection details for the database configuration. Besides that, everything should be configured via the web UI.

