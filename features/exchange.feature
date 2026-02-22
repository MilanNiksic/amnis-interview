Feature: Currency Exchange
  In order to manage currency exchanges between accounts
  As a user
  I need to be able to exchange currencies and track transactions

  Background:
    Given I add "Content-Type" header equal to "application/ld+json"

  Scenario: Complete currency exchange workflow - payin, exchange, payout
    # Setup: Create business partner, currencies, and accounts
    Given there is a business partner with data:
      | name                       | status | legalForm                 | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | Baslerstrasse 60 | Zürich | 8048 | CH      |
    Given there is a currency with data:
      | code | name        | scale | isActive |
      | CHF  | Swiss Franc | 100   | true     |
      | EUR  | Euro        | 100   | true     |
    Given there is an account with data:
      | name        | accountNumber         | businessPartner          | currency | balance |
      | CHF Account | CH9300762011623852957 | /api/business_partners/1 | CHF      | 0.00    |
      | EUR Account | CH9300762011623852958 | /api/business_partners/1 | EUR      | 0.00    |
    Given there is an exchange rate with data:
      | fromCurrency | toCurrency | rate  |
      | CHF          | EUR        | 1.1   |
      | EUR          | CHF        | 0.909 |

    # Step 1: Create a payin of 1000.00 CHF
    When I send a POST request to "/api/transactions/payin" with body:
    """
      {
        "amount": "1000.00",
        "name": "Initial CHF Payin",
        "date": "2026-02-22T10:00:00.000Z",
        "country": "CH",
        "iban": "CH5604835012345678009",
        "businessPartner": "/api/business_partners/1",
        "account": "/api/accounts/1"
      }
    """
    Then the response status code should be 201
    And the JSON node "amount" should be equal to "1000"
    And the JSON node "executed" should be true

    # Verify CHF account has 1000 CHF
    When I send a GET request to "/api/accounts/1"
    Then the response status code should be 200
    And the JSON node "balance" should be equal to "1000"
    And the JSON node "currencyCode" should be equal to "CHF"

    # Step 2: Create an exchange of 1000 CHF to 1100 EUR (rate 1.1)
    When I send a POST request to "/api/account-currency-exchanges" with body:
    """
      {
        "fromAccountId": "1",
        "toAccountId": "2",
        "amount": "1000.00"
      }
    """
    Then the response status code should be 201
    And the JSON node "fromAmount" should be equal to "1000"
    And the JSON node "toAmount" should be equal to "1100"
    And the JSON node "exchangeRate" should be equal to "1.1"

    # Verify CHF account is now empty
    When I send a GET request to "/api/accounts/1"
    Then the response status code should be 200
    And the JSON node "balance" should be equal to "0"

    # Verify EUR account has 1100 EUR
    When I send a GET request to "/api/accounts/2"
    Then the response status code should be 200
    And the JSON node "balance" should be equal to "1100"
    And the JSON node "currencyCode" should be equal to "EUR"

    # Step 3: Create a payout of 1100.00 EUR
    Given I add "Content-Type" header equal to "application/ld+json"
    When I send a POST request to "/api/transactions/payout" with body:
    """
      {
        "amount": "1100.00",
        "name": "EUR Payout",
        "date": "2026-02-22T11:00:00.000Z",
        "country": "DE",
        "iban": "DE89370400440532013000",
        "businessPartner": "/api/business_partners/1",
        "account": "/api/accounts/2"
      }
    """
    Then the response status code should be 201

    # Execute the payout
    Given I add "Content-Type" header equal to "application/merge-patch+json"
    When I send a PATCH request to "/api/transactions/4/payout/execute" with body:
    """
      {

      }
    """
    Then the response status code should be 200

    # Verify EUR account is now empty
    When I send a GET request to "/api/accounts/2"
    Then the response status code should be 200
    And the JSON node "balance" should be equal to "0"

    # Verify CHF account transactions
    Given I add "Content-Type" header equal to "application/ld+json"
    When I send a GET request to "/api/accounts/1"
    Then the response status code should be 200
    And the JSON node "transactions" should have 2 elements

    # Verify EUR account transactions
    When I send a GET request to "/api/accounts/2"
    Then the response status code should be 200
    And the JSON node "transactions" should have 2 elements

    # Verify Business Partner transactions
    When I send a GET request to "/api/business_partners/1"
    Then the response status code should be 200
    And the JSON node "transactions" should have 4 elements

  Scenario: Exchange requires different currencies
    Given there is a business partner with data:
      | name                       | status | legalForm                 | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | Baslerstrasse 60 | Zürich | 8048 | CH      |
    Given there is a currency with data:
      | code | name        | scale | isActive |
      | CHF  | Swiss Franc | 100   | true     |
    Given there is an account with data:
      | name        | accountNumber         | businessPartner          | currency | balance |
      | CHF Account | CH9300762011623852957 | /api/business_partners/1 | CHF      | 1000.00 |
      | CHF Account 2 | CH9300762011623852958 | /api/business_partners/1 | CHF      | 1000.00 |
    When I send a POST request to "/api/account-currency-exchanges" with body:
    """
      {
        "fromAccountId": 1,
        "toAccountId": 2,
        "amount": "500.00"
      }
    """
    Then the response status code should be 500
    And the response should contain "different currencies"

  Scenario: Exchange requires sufficient balance
    Given there is a business partner with data:
      | name                       | status | legalForm                 | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | Baslerstrasse 60 | Zürich | 8048 | CH      |
    Given there is a currency with data:
      | code | name        | scale | isActive |
      | CHF  | Swiss Franc | 100   | true     |
      | EUR  | Euro        | 100   | true     |
    Given there is an account with data:
      | name        | accountNumber         | businessPartner          | currency | balance |
      | CHF Account | CH9300762011623852957 | /api/business_partners/1 | CHF      | 100.00  |
      | EUR Account | CH9300762011623852958 | /api/business_partners/1 | EUR      | 0.00    |
    Given there is an exchange rate with data:
      | fromCurrency | toCurrency | rate  |
      | CHF          | EUR        | 1.1   |
      | EUR          | CHF        | 0.909 |
    When I send a POST request to "/api/account-currency-exchanges" with body:
    """
      {
        "fromAccountId": 1,
        "toAccountId": 2,
        "amount": "500.00"
      }
    """
    Then the response status code should be 500
    And the response should contain "insufficient balance"

  Scenario: Exchange requires existing exchange rate
    Given there is a business partner with data:
      | name                       | status | legalForm                 | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | Baslerstrasse 60 | Zürich | 8048 | CH      |
    Given there is a currency with data:
      | code | name        | scale | isActive |
      | CHF  | Swiss Franc | 100   | true     |
      | EUR  | Euro        | 100   | true     |
    Given there is an account with data:
      | name        | accountNumber         | businessPartner          | currency | balance |
      | CHF Account | CH9300762011623852957 | /api/business_partners/1 | CHF      | 100.00  |
      | EUR Account | CH9300762011623852958 | /api/business_partners/1 | EUR      | 0.00    |
    When I send a POST request to "/api/account-currency-exchanges" with body:
    """
      {
        "fromAccountId": 1,
        "toAccountId": 2,
        "amount": "50.00"
      }
    """
    Then the response status code should be 500
    And the response should contain "exchange rate not found"