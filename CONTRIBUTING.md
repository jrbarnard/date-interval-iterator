# Date Interval Iterator Contributing #

Before doing any work, please log the issue in the github issue tracker.

1. Fork the repo and clone locally
2. Branch off of master with the following naming convention:
    - If a feature, name as feature/descriptive-name
    - If a bugfix, name as bugfix/{issue_number}
3. Do work, commit and push branch
4. Create a pull request on GitHub

## Tests ##

Please ensure to add tests and check coverage and coding standards by running:
```
# Run tests
composer test

# Run coverage
composer test:cov

# Run codesniffer
composer test:style
```

The project aims to stay at 100% coverage.

## Coding style ##

PSR2