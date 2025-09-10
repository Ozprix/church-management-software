# Sentry Integration for Error Monitoring

## Installation

1. Install the Sentry Laravel SDK:

```bash
composer require sentry/sentry-laravel
```

2. Publish the Sentry configuration file:

```bash
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

## Configuration

1. Create a Sentry account at https://sentry.io/ if you don't have one already.

2. Create a new project in Sentry for your Laravel application.

3. Get your DSN (Data Source Name) from the Sentry project settings.

4. Add your Sentry DSN to your `.env` file:

```
SENTRY_LARAVEL_DSN=https://your-sentry-dsn-here@sentry.io/your-project-id
```

5. Configure additional Sentry options in your `.env` file:

```
SENTRY_TRACES_SAMPLE_RATE=1.0
SENTRY_PROFILES_SAMPLE_RATE=1.0
```

## Usage

Sentry will automatically capture unhandled exceptions and errors. You can also manually capture exceptions and messages:

```php
try {
    // Your code that might throw an exception
} catch (\Throwable $e) {
    \Sentry\captureException($e);
}

// Capture a message
\Sentry\captureMessage('Something went wrong');
```

## Performance Monitoring

Sentry also provides performance monitoring capabilities. You can create transactions to monitor specific parts of your application:

```php
$transaction = \Sentry\startTransaction(
    'process-donation',
    'task'
);

\Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($transaction): void {
    $scope->setSpan($transaction);
});

try {
    // Process donation logic here
} finally {
    $transaction->finish();
}
```

## User Context

You can add user context to your error reports to help with debugging:

```php
\Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
    $scope->setUser([
        'id' => auth()->id(),
        'email' => auth()->user()->email,
        'username' => auth()->user()->name,
    ]);
});
```

## Custom Context

Add additional context to your error reports:

```php
\Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
    $scope->setTag('page_locale', app()->getLocale());
    $scope->setExtra('server_name', gethostname());
});
```

## Breadcrumbs

Add breadcrumbs to track the path that led to an error:

```php
\Sentry\addBreadcrumb(new \Sentry\Breadcrumb(
    \Sentry\Breadcrumb::LEVEL_INFO,
    \Sentry\Breadcrumb::TYPE_USER,
    'auth',
    'User logged in',
    ['user_id' => auth()->id()]
));
```

## Ignoring Specific Exceptions

You can configure Sentry to ignore specific exceptions in the `config/sentry.php` file:

```php
'ignore_exceptions' => [
    \Illuminate\Auth\AuthenticationException::class,
    \Illuminate\Auth\Access\AuthorizationException::class,
    \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
],
```

## Environment-Specific Configuration

You can configure Sentry to only send errors in specific environments:

```php
'environment' => env('APP_ENV'),
'enabled_environments' => ['production', 'staging'],
```

## Deployment Tracking

Track deployments to correlate errors with specific releases:

```bash
php artisan sentry:publish --release=<release_name>
```

## Troubleshooting

If you're not seeing errors in your Sentry dashboard:

1. Verify your DSN is correct
2. Check that your environment is included in `enabled_environments`
3. Ensure your firewall allows outbound connections to Sentry
4. Check your Laravel logs for any Sentry-related errors
