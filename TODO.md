# TODO

A checklist of tasks and features to implement.

## Modernization

- [ ] Doctrine ORM: Finish implementation
  - [x] Allow configuration of backend (instead of hardcoded sqlite)
  - Remaining entities:
    - Site Configuration
    - Attachment concept
    - Management (Staff, Login Attempts, Sessions, Modlog)
    - Module Settings
    - Reports
- [ ] Migrate kx libs to a kx namespace, remove autoloader  s

## Refactoring

- [ ] Major refactoring of posting processes
- [ ] All of the javascript

## Documentation

- [ ] Object model diagram: Finish up overall design.
- [ ] Namespacing guidelines
- [ ] Docblocks and all that fun php stuff

## Tests

- [ ] Add integration tests

## kx Framework

By priority:
- [ ] Finish killing kxDb, even if it's hacky
- [ ] kxConfig (modernize configuration pattern)
- [ ] kxRequest (centralize our request handling)
- [ ] kxRouter, replace index.php, manage.php, install.php?
- [ ] kxTemplate (make standard the way template variables are set)
- [ ] kxCache (PSR-6)
- [ ] kxLogger (PSR-3)


## Edaha Features

- Site:
  - [ ] Frontpage/Index
  - [ ] Banpage
  - [ ] Post Moderation
  - [ ] Attachment Moderation

- Board types
  - [ ] Textboard
  - [ ] Imageboard
  - [ ] Oekaki (Future)
  - [ ] Upload (Future)

- Post hooks
  - [ ] Captcha
  - [ ] Standard Post rules
  - [ ] BBCode Formatter
  - [ ] Markdown Formatter
  - [ ] Wordfilter

- Conveniences
  - [ ] Watched Threads
  - [ ] Post Spy


## Deployment

- [ ] Production best-practices

---

_Last updated: 2026-07-27_
