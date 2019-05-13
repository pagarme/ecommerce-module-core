# features/BuyUsingCreditCard.feature
Feature: Buy Using Credit Card
  In order to test the order creation by payment module
  As a developer
  I need to be able to create a successful order using a credit card

  Background:
    Given a new session
    And I define failure screenshot dir as "failureScreenshots"

  @javascript
  Scenario: Buying a product using a credit card
    Given I add a product to shopping cart
