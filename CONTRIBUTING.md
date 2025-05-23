# Contributing to Edaha

Thank you for your interest in contributing to Edaha! We welcome contributions from everyone.

## Before You Start

- **Open an Issue First:**  
  Please open an issue to discuss your proposed change before submitting a pull request. This helps us coordinate efforts and avoid duplicate work.

## Development Guidelines

- **Coding Standards:**  
  Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) for PHP code. Please declare return types on methods where possible.

- **Testing:**  
  All new code must include tests, and these tests must pass before your pull request will be considered.  
  Run tests locally using:
  ```
  docker build -t php-docker-image-test --progress plain --no-cache --target test .
  ```
  Or use the helper scripts `test.sh` and `test_from_scratch.sh`.

- **Branching:**  
  There is no strict branching model. Please fork the repository and submit your pull request from your fork.

- **Pull Requests:**  
  - Reference the related issue(s) in your pull request description.
  - Clearly describe your changes and why they are needed.
  - Ensure your code passes all tests and adheres to formatting rules.

- **Community Guidelines:**  
  Be kind and respectful in all interactions.

- **Areas Needing Special Attention:**  
  Some areas may not be accepting contributions or may need special care. Please see [TODO.md](./TODO.md) for details.

## Questions?

If you have any questions, open an issue or start a discussion.

Thank you for helping make Edaha better!