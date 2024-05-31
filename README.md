# Banking System

## Setup Instruction

1. Clone The repository:

`git clone https://github.com/MdJusef/banking-system.git` \
`cd banking-system` 

2. Install Dependencies \
`composer install`

3. Copy `.env.example` to `.env` and configure your database: \
modify database name \
`DB_DATABASE=banking_system_db`\
`php artisan key:generate`

4. Generate Jwt Secret \
`php artisan jwt:secret`

5. Run Migrations \
`php artisan migrate`

7. Serve The Application \
`php artisan serve`

## API Endpoints
* POST /users: Create a new user.
* POST /login: Login a user.
* GET /: Show all transactions and current balance.
* GET /deposit: Show all deposited transactions.
* POST /deposit: Deposit amount to user account.
* GET /withdrawal: Show all withdrawal transactions.
* POST /withdrawal: Withdraw amount from user account.


