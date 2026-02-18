# Description
This project is a very dummy version of our system, where you can manually create a pay-in into an account and a payout from an account. The system allows a payout only if there is enough money in a given company’s account. This version can handle only 1 single currency.

# Tasks

1. Introduce the possibility of having multiple currency accounts in the system. Keep in mind, that we need the possibility to show in the API / UI transactions on a specific currency account.

2. Introduce the “currency exchange” function, which would make it possible to convert an amount from one currency to another. The currency exchange would need at least the following properties:
- from_currency
- to_currency
- from_amount
- to_amount
- exchange rate - this would hold the information, with which rate the customer could exchange.

Expect that in reality there are at least 15 more fields.

Example: Sell 1000.00 CHF and Buy EUR, with an exchange_rate of “1.1”. In this example, the customer would receive 1100.00 EUR → sell_amount * exchange_rate
For the sake of this task, you can hardcode the exchange rate in a function to be always the same for any currency pair.

## How to test, if it works correctly

- Create a payin of 1000.00 CHF
- Create an exchange of 1000.00 CHF to 1100.00 EUR with an exchange rate of 1.1
- Create a payout of 1100.00 EUR

After all 3 transactions are executed, both the CHF and the EUR should be empty.
If you fetch the transactions on the 
- CHF account: you should see +1000.00 CHF and -1000.00 CHF (payin and exchange)
- EUR account: you should see +1100.00 EUR and - 1100.00 EUR (exchange and payout)

Please upload your solution to a public github repository and create a merge request with your changes.
The UI serves only a trivial control purpose, it does not need any design upgrade or replacament, but the requested changes should be reflected there as well.

# Installation and usage

## Requirements on your local machine (without dockerization)
 
1. At least PHP 8.2   
2. Composer 

## Install dependencies 

1. Install symfony CLI: https://symfony.com/download
2. Use composer to install packages: `composer install`

## Prepare the database 

Creating an empty database 

> composer init-database

OR creating a database with fixtures 
 
> composer init-database-with-fixtures

## Start local server for the project

`symfony server:start`

> You can reach the symfony application here: http://127.0.0.1:8000/

# API endpoints

You can check the existing API Platform endpoints here: http://127.0.0.1:8000/api/docs

> It is an automatically generated documentations by API Platform, if you change something this will be regenerated automatically.

# Tests

## Behat tests

> composer behat
