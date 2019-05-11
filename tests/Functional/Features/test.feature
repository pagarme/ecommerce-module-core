# features/test.feature
Feature: Test
  In order to test Functional Test Integration
  As a developer
  I need to be able to login on Magento 2

  Background:
    Given a new session
    And I define failure screenshot dir as "failureScreenshots"

  @javascript
  Scenario: Login into Magento 2
    Given I am on "http://127.0.0.1/admin"
    Then I wait for text "Username" to appear, for 20 seconds
    #And I fill in "username" with "admin"
    #And I fill in "login" with "@teste123"
    #And I click in element "button"
    #And I wait for text "Mundipagg" to appear, for 15 seconds

