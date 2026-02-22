Feature: Account
  In order to manage accounts
  As a user
  I need to be able manage accounts through REST API

  Background:
    Given I add "Content-Type" header equal to "application/ld+json"

  Scenario: Get a list of accounts
    Given there is a business partner with data:
      | name                       | status | legalForm                 | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | Baslerstrasse 60 | Zürich | 8048 | CH      |
      | AMNIS Europe AG            | active | limited_liability_company | Gewerbeweg 15    | Vaduz  | 9490 | LI      |
    Given there is a currency with data:
      | code | name              | scale | isActive |
      | CHF  | Swiss Franc       | 100   | true     |
      | EUR  | Euro              | 100   | true     |
    Given there is an account with data:
      | name          | accountNumber         | businessPartner          | currency | balance |
      | CHF Account 1 | CH9300762011623852957 | /api/business_partners/1 | CHF      | 1000.00 |
      | EUR Account 1 | CH9300762011623852958 | /api/business_partners/1 | EUR      | 500.00  |
      | CHF Account 2 | CH9300762011623852959 | /api/business_partners/2 | CHF      | 2000.00 |
    When I send a GET request to "/api/accounts"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 3 elements

  Scenario: Get a single account
    Given there is a business partner with data:
      | name                       | status | legalForm                 | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | Baslerstrasse 60 | Zürich | 8048 | CH      |
    Given there is a currency with data:
      | code | name        | scale | isActive |
      | CHF  | Swiss Franc | 100   | true     |
    Given there is an account with data:
      | name          | accountNumber         | businessPartner          | currency | balance |
      | CHF Account 1 | CH9300762011623852957 | /api/business_partners/1 | CHF      | 1000.00 |
    When I send a GET request to "/api/accounts/1"
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/api/accounts/1"
    And the JSON node "name" should be equal to the string "CHF Account 1"
    And the JSON node "accountNumber" should be equal to "CH9300762011623852957"
    And the JSON node "balance" should be equal to "1000"
    And the JSON node "currencyCode" should be equal to "CHF"
    And the JSON node "businessPartner" should be equal to the string "/api/business_partners/1"

  Scenario: Create an account
    Given there is a business partner with data:
      | name                       | status | legalForm                 | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | Baslerstrasse 60 | Zürich | 8048 | CH      |
    Given there is a currency with data:
      | code | name        | scale | isActive |
      | CHF  | Swiss Franc | 100   | true     |
    When I send a POST request to "/api/accounts" with body:
    """
      {
        "name": "CHF New Account",
        "accountNumber": "CH9300762011623852960",
        "businessPartner": "/api/business_partners/1",
        "currency": "/api/currencies/1",
        "balance": 2500
      }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/api/accounts/1"
    And the JSON node "name" should be equal to the string "CHF New Account"
    And the JSON node "balance" should be equal to 2500
    And the JSON node "currencyCode" should be equal to "CHF"

  Scenario: Get accounts by business partner
    Given there is a business partner with data:
      | name                       | status | legalForm                 | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | Baslerstrasse 60 | Zürich | 8048 | CH      |
      | AMNIS Europe AG            | active | limited_liability_company | Gewerbeweg 15    | Vaduz  | 9490 | LI      |
    Given there is a currency with data:
      | code | name        | scale | isActive |
      | CHF  | Swiss Franc | 100   | true     |
      | EUR  | Euro        | 100   | true     |
    Given there is an account with data:
      | name          | accountNumber         | businessPartner          | currency | balance |
      | CHF Account 1 | CH9300762011623852957 | /api/business_partners/1 | CHF      | 1000.00 |
      | EUR Account 1 | CH9300762011623852958 | /api/business_partners/1 | EUR      | 500.00  |
      | CHF Account 2 | CH9300762011623852959 | /api/business_partners/2 | CHF      | 2000.00 |
    When I send a GET request to "/api/business_partners/1/accounts"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 2 elements
    And the JSON node "hydra:member[0].name" should be equal to "CHF Account 1"
    And the JSON node "hydra:member[0].accountNumber" should be equal to "CH9300762011623852957"
    And the JSON node "hydra:member[0].balance" should be equal to 1000
    And the JSON node "hydra:member[0].businessPartner" should be equal to "/api/business_partners/1"
    And the JSON node "hydra:member[0].currencyCode" should be equal to "CHF"
    And the JSON node "hydra:member[1].name" should be equal to "EUR Account 1"
    And the JSON node "hydra:member[1].accountNumber" should be equal to "CH9300762011623852958"
    And the JSON node "hydra:member[1].balance" should be equal to 500
    And the JSON node "hydra:member[1].businessPartner" should be equal to "/api/business_partners/1"
    And the JSON node "hydra:member[1].currencyCode" should be equal to "EUR"

  Scenario: Cannot create duplicate account with same currency for same partner
    Given there is a business partner with data:
      | name                       | status | legalForm                 | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | Baslerstrasse 60 | Zürich | 8048 | CH      |
    Given there is a currency with data:
      | code | name        | scale | isActive |
      | CHF  | Swiss Franc | 100   | true     |
    Given there is an account with data:
      | name          | accountNumber         | businessPartner          | currency | balance |
      | CHF Account 1 | CH9300762011623852957 | /api/business_partners/1 | CHF      | 1000.00 |
    When I send a POST request to "/api/accounts" with body:
    """
      {
        "name": "CHF Account 2",
        "accountNumber": "CH9300762011623852961",
        "businessPartner": "/api/business_partners/1",
        "currency": "/api/currencies/1",
        "balance": 5000
      }
    """
    Then the response status code should be 422
