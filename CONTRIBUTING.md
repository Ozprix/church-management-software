# Contributing to Church Management System

Thank you for considering contributing to the Church Management System! We welcome any contributions that can help improve the application.

## Code of Conduct

In the interest of fostering an open and welcoming environment, we as contributors and maintainers pledge to make participation in our project and our community a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

## How Can I Contribute?

### Reporting Bugs

- Ensure the bug was not already reported by searching on GitHub under [Issues](https://github.com/yourusername/church-management/issues).
- If you're unable to find an open issue addressing the problem, [open a new one](https://github.com/yourusername/church-management/issues/new). Be sure to include a title and clear description, as much relevant information as possible, and a code sample or an executable test case demonstrating the expected behavior.

### Suggesting Enhancements

- Open a new issue with the enhancement suggestion
- Clearly describe the enhancement and why you believe it would be useful
- Include any relevant code, documentation, or screenshots

### Pull Requests

1. Fork the repository and create your branch from `main`
2. If you've added code that should be tested, add tests
3. If you've changed APIs, update the documentation
4. Ensure the test suite passes
5. Make sure your code lints
6. Issue that pull request!

## Development Setup

1. Fork the repository on GitHub
2. Clone your fork locally
3. Install dependencies:
   ```bash
   composer install
   npm install
   ```
4. Create a `.env` file and set up your environment
5. Generate an application key:
   ```bash
   php artisan key:generate
   ```
6. Run the database migrations:
   ```bash
   php artisan migrate --seed
   ```
7. Start the development server:
   ```bash
   php artisan serve
   ```

## Coding Standards

- Follow the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Write meaningful commit messages
- Keep your code DRY (Don't Repeat Yourself)
- Write tests for new features and bug fixes
- Document your code with PHPDoc blocks

## Testing

Run the test suite:

```bash
php artisan test
```

## Version Control

- Create a new branch for each feature or bugfix
- Use descriptive branch names (e.g., `feature/user-authentication` or `bugfix/login-validation`)
- Write clear, concise commit messages
- Rebase your branch on the latest main before submitting a pull request

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
