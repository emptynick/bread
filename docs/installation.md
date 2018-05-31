# Installation

### 1. Clone the hook
From your root directory clone the hook into the hooks dir
```bash
git clone https://github.com/emptynick/voyager-bread.git hooks/voyager-bread
```
### 2. Install the hook
After you cloned the hook, you can install it.
```bash
php artisan hook:install voyager-bread
```
Tip: if you get any errors here, run `composer install voyager-bread`
### 3. Enable the hook
You can do this through Voyagers Hook-UI or by running the following command
```bash
php artisan hook:enable voyager-bread
```