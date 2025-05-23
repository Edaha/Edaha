
# Edaha
Edaha is a modular, object oriented image BBS software written in PHP, written using the custom Kx framework and loosely based on Kusaba X. It is designed to be modular (any part of the code can be extended with modules) and easy to set up and use. 

# Development

## Code Formatting Rules

Just use [PSR-12](https://www.php-fig.org/psr/psr-12/). Go a step further and declare return types on methods if you remember to. 

## Local Development

### Starting the local development environment

1. `git clone https://github.com/Edaha/Edaha.git`
2. `docker compose up --build`
3. (Optional) Enable Docker Watch

### Running tests

You can run tests by running the following command:

`docker build -t php-docker-image-test --progress plain --no-cache --target test .`

Or, use the helper scripts `test.sh` and `test_from_scratch.sh`.

## Contributing

See [CONTRIBUTING.md](./CONTRIBUTING.md).

