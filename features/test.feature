# features/test.feature
Feature: Test
  In order to test Functional Test Integration
  As a developer
  I need to be able to login on Magento 2

  @javascript
  Scenario: Login into Magento 2
    Given I am on "/admin"
    Then I wait for text "Username" to appear, for 15 seconds
    And I fill in "username" with "admin"
    And I fill in "login" with "@teste123"
    And I click in element "button"
    And I wait for text "Mundipagg" to appear, for 15 seconds

  @javascript
  Scenario: I go to Mundipagg
    Given I go to Mundipagg



