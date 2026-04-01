# Local Setup and Usage (Windows + VSCode + XAMPP)

This guide covers two things:
1. How to run the app locally (VSCode + XAMPP + `php artisan serve`).
2. How to use the main screens of the web app after you log in.

## Quick Script (Recommended)
Run the setup script in the VSCode terminal from the project root:

```powershell
.\scripts\local-dev.ps1 -Seed
```

What it does:
1. Ensures `.env` exists.
2. Generates `APP_KEY` if missing.
3. Installs PHP and JS dependencies.
4. Runs migrations and seeds a default user.

The seed creates this login:
`Email: test@example.com`
`Password: password`

## Manual Setup (Step by Step)
1. Open VSCode.
2. Open the project folder:
   `C:\Users\Admin\Desktop\web ni kasi\loan-tracker-system\app`
3. Open the VSCode terminal.
4. Open XAMPP Control Panel, click `Start` for `Apache` and `MySQL`.
5. Ensure your database config matches XAMPP:
   Check `.env` for:
   `DB_HOST=127.0.0.1`
   `DB_PORT=3307`
   `DB_DATABASE=loan_system`
   `DB_USERNAME=root`
   `DB_PASSWORD=`
6. Create the database:
   Go to `http://localhost/phpmyadmin` and create a database named `loan_system`.
7. Install PHP dependencies:
```powershell
composer install
```
8. Install JS dependencies:
```powershell
npm install
```
9. Run migrations (and seed a default user):
```powershell
php artisan migrate --seed
```
10. Start the Laravel server:
```powershell
php artisan serve
```
11. Optional (for frontend assets):
```powershell
npm run dev
```
12. Open the app:
`http://127.0.0.1:8000`

## Login Notes
If you ran `--seed`, use:
`test@example.com` / `password`

If you register a new user:
1. Go to `/register`.
2. After registering, you may see an email verification screen.
3. The seeded user already has `email_verified_at` set, so it bypasses verification.

## How To Use The Web App
After login, use the left sidebar navigation.

### 1. Borrowers
1. Go to `Borrowers`.
2. Click `Add New Borrower`.
3. Fill in First Name, Last Name, Phone, and optionally upload an ID document.
4. Click `Save`.
5. Click `View` to open the borrower detail page.

### 2. Loans
1. Go to `Loans`.
2. Use the form to add a new loan.
3. Choose a borrower, set amount, interest, and term.
4. Save and check the loan list for status and remaining balance.

### 3. Payments
1. Go to `Payments`.
2. Select the related loan.
3. Enter payment details and save.
4. The remaining balance updates on the loan.

### 4. Funds
1. Go to `Funds` to track incoming/outgoing funds.

### 5. Paid Loans
1. Go to `Paid Loans` to see loans marked as fully paid.

### 6. Investor Returns
1. Go to `Investor Returns` to view profit summaries.
2. Use the PDF export if needed.

### 7. Income & Expenses
1. Go to `Income & Expenses` for a summary view.

### 8. Reports
1. Go to `Reports` for printable or exportable summaries.

### 9. Motor Rentals
1. Go to `Motor Rentals` to manage rental records.

## Roles and Admin Pages
Some pages are restricted to `owner` and `admin` roles.
The seeded user is `owner` and can access:
`Integrity Check`, `Role Management`, and `Backup Management`.

## Optional Sample Data
If you want extra sample records:
```powershell
php artisan db:seed --class=BorrowerSeeder
php artisan db:seed --class=LoanSeeder
```
