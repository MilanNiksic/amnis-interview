Feature: Transaction
  In order to manage transactions
  As a user
  I need to be able manage transactions through REST API

  Background:
    Given I add "Content-Type" header equal to "application/ld+json"

  Scenario: Get a list of transactions
    Given there is a business partner with data:
      | name                       | status | legalForm                 | balance | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | 400     | Baslerstrasse 60 | Zürich | 8048 | CH      |
      | AMNIS Europe AG            | active | limited_liability_company | 200     | Gewerbeweg 15    | Vaduz  | 9490 | LI      |
    Given create a transaction with data:
      | name                       | amount | date                | executed | type   | country | iban                  | businessPartner          |
      | AMNIS Treasury Services AG | 100    | 2024-07-01 13:32:45 | false    | payin  | CH      | CH5604835012345678009 | /api/business_partners/1 |
      | AMNIS Europe AG            | 100    | 2024-07-01 13:32:45 | false    | payout | LI      | LI7408806123456789012 | /api/business_partners/2 |
    When I send a GET request to "/api/transactions"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 2 elements

  Scenario: Get a transaction
    Given there is a business partner with data:
      | name                       | status | legalForm                 | balance | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | 400     | Baslerstrasse 60 | Zürich | 8048 | CH      |
    Given create a transaction with data:
      | name                       | amount | date                | executed | type   | country | iban                  | businessPartner          |
      | AMNIS Treasury Services AG | 100    | 2024-07-01 13:32:45 | false    | payin  | CH      | CH5604835012345678009 | /api/business_partners/1 |
    When I send a GET request to "/api/transactions/1"
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/api/transactions/1"
    And the JSON node "name" should be equal to the string "AMNIS Treasury Services AG"
    And the JSON node "amount" should be equal to "100"
    And the JSON node "date" should be equal to the string "2024-07-01T13:32:45+00:00"
    And the JSON node "executed" should be false
    And the JSON node "type" should be equal to the string "payin"
    And the JSON node "country" should be equal to "CH"
    And the JSON node "iban" should be equal to "CH5604835012345678009"
    And the JSON node "businessPartner" should be equal to the string "/api/business_partners/1"

  Scenario: Create payin transaction
    Given there is a business partner with data:
      | name                       | status | legalForm                 | balance | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | 400     | Baslerstrasse 60 | Zürich | 8048 | CH      |
    When I send a POST request to "/api/transactions/payin" with body:
    """
      {
        "amount": "300",
        "name": "AMNIS Treasury Services AG",
        "date": "2024-07-12T09:08:32.563Z",
        "country": "CH",
        "iban": "CH5604835012345678009",
        "businessPartner": "/api/business_partners/1"
      }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/api/transactions/1"
    And the JSON node "name" should be equal to the string "AMNIS Treasury Services AG"
    And the JSON node "amount" should be equal to "300"
    And the JSON node "date" should be equal to the string "2024-07-12T09:08:32+00:00"
    And the JSON node "executed" should be true
    And the JSON node "type" should be equal to the string "payin"
    And the JSON node "country" should be equal to "CH"
    And the JSON node "iban" should be equal to "CH5604835012345678009"
    And the JSON node "businessPartner" should be equal to the string "/api/business_partners/1"

  Scenario: Create payout transaction
    Given there is a business partner with data:
      | name                       | status | legalForm                 | balance | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | 400     | Baslerstrasse 60 | Zürich | 8048 | CH      |
    When I send a POST request to "/api/transactions/payout" with body:
    """
      {
        "amount": "300",
        "name": "AMNIS Treasury Services AG",
        "date": "2024-07-12T09:08:32.563Z",
        "country": "CH",
        "iban": "CH5604835012345678009",
        "businessPartner": "/api/business_partners/1"
      }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/api/transactions/1"
    And the JSON node "name" should be equal to the string "AMNIS Treasury Services AG"
    And the JSON node "amount" should be equal to "300"
    And the JSON node "date" should be equal to the string "2024-07-12T09:08:32+00:00"
    And the JSON node "executed" should be false
    And the JSON node "type" should be equal to the string "payout"
    And the JSON node "country" should be equal to "CH"
    And the JSON node "iban" should be equal to "CH5604835012345678009"
    And the JSON node "businessPartner" should be equal to the string "/api/business_partners/1"

  Scenario: Execute payout transaction
    Given there is a business partner with data:
      | name                       | status | legalForm                 | balance | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | 400     | Baslerstrasse 60 | Zürich | 8048 | CH      |
    When I send a POST request to "/api/transactions/payout" with body:
    """
      {
        "amount": "300",
        "name": "AMNIS Treasury Services AG",
        "date": "2024-07-12T09:08:32.563Z",
        "country": "CH",
        "iban": "CH5604835012345678009",
        "businessPartner": "/api/business_partners/1"
      }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/api/transactions/1"
    And the JSON node "name" should be equal to the string "AMNIS Treasury Services AG"
    And the JSON node "amount" should be equal to "300"
    And the JSON node "date" should be equal to the string "2024-07-12T09:08:32+00:00"
    And the JSON node "executed" should be false
    And the JSON node "type" should be equal to the string "payout"
    And the JSON node "country" should be equal to "CH"
    And the JSON node "iban" should be equal to "CH5604835012345678009"
    And the JSON node "businessPartner" should be equal to the string "/api/business_partners/1"
    Given I add "Content-Type" header equal to "application/merge-patch+json"
    When I send a PATCH request to "/api/transactions/1/payout/execute" with body:
    """
      {

      }
    """
    Then the response status code should be 200
    And the JSON node "executed" should be true
