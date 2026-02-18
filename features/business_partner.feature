Feature: Business partner
  In order to manage business partners
  As a user
  I need to be able manage business partners through REST API

  Background:
    Given I add "Content-Type" header equal to "application/ld+json"

  Scenario: Get a list of business partners
    Given there is a business partner with data:
      | name                       | status | legalForm                 | balance | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | 400     | Baslerstrasse 60 | Zürich | 8048 | CH      |
      | AMNIS Europe AG            | active | limited_liability_company | 200     | Gewerbeweg 15    | Vaduz  | 9490 | LI      |
    When I send a GET request to "/api/business_partners"
    Then the response status code should be 200
    And the JSON node "hydra:member" should have 2 elements

  Scenario: Get a business partner
    Given there is a business partner with data:
      | name                       | status | legalForm                 | balance | address          | city   | zip  | country |
      | AMNIS Treasury Services AG | active | limited_liability_company | 400     | Baslerstrasse 60 | Zürich | 8048 | CH      |
    When I send a GET request to "/api/business_partners/1"
    Then the response status code should be 200
    And the JSON node "@id" should be equal to the string "/api/business_partners/1"
    And the JSON node "name" should be equal to the string "AMNIS Treasury Services AG"
    And the JSON node "status" should be equal to the string "active"
    And the JSON node "legalForm" should be equal to the string "limited_liability_company"
    And the JSON node "balance" should be equal to 400
    And the JSON node "address" should be equal to the string "Baslerstrasse 60"
    And the JSON node "city" should be equal to the string "Zürich"
    And the JSON node "zip" should be equal to "8048"
    And the JSON node "country" should be equal to the string "CH"

  Scenario: Create a business partners
    When I send a POST request to "/api/business_partners" with body:
    """
      {
        "name": "AMNIS Treasury Services AG",
        "status": "active",
        "legalForm": "limited_liability_company",
        "address": "Baslerstrasse 60",
        "city": "Zürich",
        "zip": "8048",
        "country": "CH"
      }
    """
    Then the response status code should be 201
    And the JSON node "@id" should be equal to the string "/api/business_partners/1"
    And the JSON node "name" should be equal to the string "AMNIS Treasury Services AG"
    And the JSON node "status" should be equal to the string "active"
    And the JSON node "legalForm" should be equal to the string "limited_liability_company"
    And the JSON node "address" should be equal to the string "Baslerstrasse 60"
    And the JSON node "city" should be equal to the string "Zürich"
    And the JSON node "zip" should be equal to "8048"
    And the JSON node "country" should be equal to the string "CH"
