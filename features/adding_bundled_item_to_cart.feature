@shopping_cart
Feature: Adding bundled product to cart
    In order to buy compounded products
    As a Visitor
    I want to be able to buy products in bundles

    Background:
        Given the store operates on a single channel in EUR currency
        And the store has a product association type "Product Bundle" with a code "bundled_products"
        And the store has "Symfony Live Conference Ticket", "Symfony Live Workshop Ticket" and "Symfony Live Bundled Ticket" products
        And the product "Symfony Live Bundled Ticket" has an association "Product Bundle" with products "Symfony Live Conference Ticket" and "Symfony Live Workshop Ticket"

    @ui
    Scenario: Adding a bundled product to the cart
        When I add product "Symfony Live Bundled Ticket" to the cart
        Then I should be on my cart summary page
        And I should be notified that the product has been successfully added
        And I should see "Symfony Live Conference Ticket" with quantity 1 in my cart
        And I should see "Symfony Live Workshop Ticket" with quantity 1 in my cart
