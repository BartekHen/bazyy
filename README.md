# Dziennik Szkolny (School Register System)

A PHP-based school register system with secure password hashing implementation.

## Features

- User registration with password hashing
- Secure login authentication
- Password migration script for existing users
- SQL injection protection using prepared statements

## Security Features

### Password Hashing

This system implements secure password storage using PHP's built-in password hashing functions:

- **`password_hash()`**: Used during registration to hash passwords with bcrypt algorithm
- **`password_verify()`**: Used during login to verify passwords against stored hashes
- **Bcrypt algorithm**: Provides strong, adaptive hashing with automatic salt generation

### Database Schema

The `uzytkownik` (user) table stores passwords in a `VARCHAR(255)` column to accommodate hashed passwords:

```sql
CREATE TABLE `uzytkownik` (
  `id_uzytkownika` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `haslo` varchar(255) NOT NULL,  -- Stores hashed passwords
  `imie` varchar(40) NOT NULL,
  `nazwisko` varchar(60) NOT NULL,
  `email` varchar(50) NOT NULL,
  `czy_aktywny` varchar(3) NOT NULL DEFAULT 'TAK',
  `rola` varchar(30) NOT NULL,
  `telefon` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Usage

### Registration Flow

The `register.php` file handles new user registration:

```php
// Hash the user's password before storing it
$hashedPassword = password_hash($userPassword, PASSWORD_BCRYPT);

// Use prepared statement to prevent SQL injection
$stmt = mysqli_prepare($connection, "INSERT INTO uzytkownik (login, haslo, imie, nazwisko, email) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sssss", $userLogin, $hashedPassword, $userName, $userSurname, $userEmail);
mysqli_stmt_execute($stmt);
```

**Example POST request:**
```
POST /register.php
Content-Type: application/x-www-form-urlencoded

login=john.doe&password=mySecurePass123&name=John&surname=Doe&email=john@example.com
```

### Login Flow

The `login.php` file handles user authentication:

```php
// Fetch the stored hashed password
$stmt = mysqli_prepare($connection, "SELECT haslo FROM uzytkownik WHERE login = ?");
mysqli_stmt_bind_param($stmt, "s", $userLogin);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $storedHashedPassword = $row['haslo'];
    
    // Verify the password
    if (password_verify($userPassword, $storedHashedPassword)) {
        echo "Login successful!";
    }
}
```

**Example POST request:**
```
POST /login.php
Content-Type: application/x-www-form-urlencoded

login=john.doe&password=mySecurePass123
```

### Password Migration

For existing systems with plaintext passwords, use `password_migration.php` to hash all passwords:

```bash
php password_migration.php
```

This script:
1. Fetches all user passwords from the database
2. Checks if passwords are already hashed (bcrypt hashes start with `$2y$`)
3. Hashes plaintext passwords using `password_hash()`
4. Updates the database with hashed passwords
5. Reports the number of updated and skipped passwords

**Safety features:**
- Idempotent: Can be run multiple times safely
- Skips already-hashed passwords
- Uses prepared statements to prevent SQL injection

## Test Credentials

The database comes with pre-configured test accounts (all passwords are hashed):

| Login | Password | Role |
|-------|----------|------|
| admin | admin123 | admin |
| jkowal | szkola123 | nauczyciel (teacher) |
| jkowal88 | uczen123 | uczen (student) |
| jan.nowak60@szkola.pl | start123 | uczen (student) |

## Database Setup

1. Import the database schema:
```bash
mysql -u root -p < dziennik_szkolny.sql
```

2. The database will be created with:
   - Pre-configured tables
   - Sample data
   - All passwords already hashed

## Security Best Practices

This system implements several security measures:

1. **Password Hashing**: All passwords are hashed using bcrypt before storage
2. **Prepared Statements**: All SQL queries use prepared statements to prevent SQL injection
3. **No Plaintext Storage**: Passwords are never stored in plaintext
4. **Bcrypt Algorithm**: Automatically includes salt and cost factor for security
5. **Password Verification**: Uses `password_verify()` which is timing-attack safe

## Requirements

- PHP 7.0 or higher (for password_hash and password_verify functions)
- MySQL 5.5 or higher
- mysqli PHP extension

## File Structure

```
.
├── login.php                 # User authentication
├── register.php             # User registration
├── password_migration.php   # Script to hash existing passwords
├── dziennik_szkolny.sql     # Database schema and data
├── dziennik/                # Application modules
│   ├── admin/               # Admin panel
│   ├── nauczyciel/          # Teacher panel
│   └── rodzic/              # Parent panel
└── README.md                # This file
```

## Development

### Testing Registration

```bash
curl -X POST http://localhost/register.php \
  -d "login=testuser&password=testpass123&name=Test&surname=User&email=test@example.com"
```

### Testing Login

```bash
curl -X POST http://localhost/login.php \
  -d "login=testuser&password=testpass123"
```

### Testing Password Migration

```bash
php password_migration.php
# Output:
# Password migration completed!
# Updated: 11 passwords
# Skipped: 0 already hashed passwords
```

## License

This project is for educational purposes.