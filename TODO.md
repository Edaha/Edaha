# TODO

A checklist of tasks and features to implement.

## Modernization

- [ ] Doctrine ORM: Finish implementation
  - Allow configuration of backend (instead of hardcoded sqlite)
  - Remaining entities:
    - Site Configuration
    - Attachment concept
    - Management (Staff, Login Attempts, Sessions, Modlog)
    - Module Settings
    - Reports
- [ ] Migrate kx libs to a kx namespace, remove autoloaders
- [ ] Major refactoring of posting processes

## Refactoring

- [ ] kxRequest: Make $environment->request a class
- [ ] All of the javascript

## Documentation

- [ ] Object model diagram
- [ ] Namespacing guidelines
- [ ] Docblocks and all that fun php stuff

## Tests

- [ ] Add integration tests

## Features (Modules)

- [ ] Parser: Wordfilter
- [ ] Ban Files ("Banned Hashes")
- [ ] Watched Threads
- [ ] Post Spy
- [ ] Board: Oekaki
- [ ] Captcha

## Deployment

- [ ] Production best-practices

---

_Last updated: 2025-05-22_
