# Overall Flow

If kx was originally a knockoff of Drupal, now it's essentially a knockoff of Slim.

However, calling it a knockoff isn't quite right. Ultimately, the goal here is that the Edaha software consists of two parts:

1. The framework itself, known as "kx"
2. The Business Logic of a *Board Software, known as "Edaha"

PHP has come a long, long way from when kusaba nee trevorchan was first created, and a lot has changed since then. The PHP Framework Interop Group (PHP-FIG) now exists and publishes PHP Standards Recommendations that make PHP development look a lot different than the Wild Wild West of the '00s. PHP itself has continued to evolve and become safer and easier to write in. It's a great time to be making new software.

# The kx Framework

The kx Framework is our custom framework for the web application. "Custom framework" may make a lot of people shudder, because why do we need Yet Another One?

Well, really, we don't. We could just use something off-the-shelf and build Edaha within that and ultimately end up in the same place. But Edaha, and the kx framework, are a bit of a love-letter to the internet of old. We are doing this because we can, because the internet lets us. We make *boards because we can, because the internet lets us. The internet is an amazing thing, despite its current state. Also, it's a learning experience. It's fun to learn.

The kx framework is ultimately tailored for *board software, but should be flexible enough that one could theoretically create any type of web application in it.

The expected lifecycle for a kx Application is very similar to Slim:

1. Instantiation and configuration of the Application
2. Route definitions
3. Application execution

The kx Framework will provide some standard middleware:

1. Twig templating middleware
2. Error handling

