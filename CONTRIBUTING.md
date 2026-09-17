# Contributing to PassVault

Thank you for considering contributing to PassVault! This document provides guidelines and information for contributors.

## How to Contribute

### Reporting Bugs

1. Check existing [issues](https://github.com/Derek-Leiva/VaultContrasennas_Laravel/issues) to avoid duplicates
2. Open a new issue using the **Bug Report** template
3. Include clear steps to reproduce the problem
4. Provide environment details (PHP version, Laravel version, etc.)

### Suggesting Features

1. Open a new issue using the **Feature Request** template
2. Describe the problem your feature would solve
3. Explain how you envision the feature working
4. Consider the impact on existing functionality

### Pull Requests

1. Fork the repository
2. Create a feature branch from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. Make your changes following our coding standards
4. Write or update tests as needed
5. Update documentation if required
6. Commit with clear, descriptive messages
7. Push to your fork and submit a Pull Request

## Development Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL 8.0+

### Installation

```bash
# Clone your fork
git clone https://github.com/your-username/VaultContrasennas_Laravel.git
cd VaultContrasennas_Laravel

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env, then run:
php artisan migrate
```

## Coding Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style
- Use meaningful variable and function names
- Add type hints and return types
- Write comments in English
- Keep functions focused and small

### Laravel Conventions

- Use Eloquent models for database interactions
- Follow Laravel naming conventions
- Use form requests for validation
- Use API Resources for JSON responses

### Frontend

- Follow existing CSS patterns
- Use Bootstrap 5 components
- Keep JavaScript clean and modular

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## Security

**Do NOT open public issues for security vulnerabilities.**

If you discover a security issue, please report it responsibly following the guidelines in [SECURITY.md](SECURITY.md).

## Questions?

Open an issue or reach out to the maintainers. We're happy to help!

Thank you for contributing!
