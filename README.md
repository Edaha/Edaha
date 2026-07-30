
# Edaha
Edaha is a modular, object oriented image BBS software written in PHP, written using the custom Kx framework and loosely based on Kusaba X. It is designed to be modular (any part of the code can be extended with modules) and easy to set up and use. 

# Development

## Code Formatting Rules

Follow the latest [PER Coding Style](https://www.php-fig.org/per/coding-style/). The repo is set up to use [pre-commit](https://pre-commit.com/) and includes [.pre-commit-config.yaml]() to automatically run php-cs-fixer (after you've run `composer install`, as it expects to find it in a `vendor/` folder at the project's root).

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

## Repo Layout

/.vscode: Xdebug configuration for use with the Xdebug VS Code Extension
/bin: The Doctrine Console plus simple bash scripts for common commands. [Read the docs](https://www.doctrine-project.org/projects/doctrine-bundle/en/3.2/doctrine-console.html)
/docker: Configuration files used for the development containers
/ref: Currently just scratch design work that may or may not accurately reflect implementation
/src: The primary application
/tests: Self-explanatory
