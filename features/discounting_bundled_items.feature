@shopping_cart
Feature: Adding bundled product to cart
    In order to be more convinced about buing products in bundles
    As a Visitor
    I want to have price reduced when buying product bundles

    Background:
        Given the store operates on a single channel in EUR currency
        And the store has a product association type "Product Bundle" with a code "bundled_products"
        And the store has a product "Symfony Con 2017 Conference Ticket" priced at "€249"
        And the store has a product "Symfony Con 2017 Workshop Ticket" priced at "€690"
        And the store has a product "Symfony Con 2017 Bundled Ticket" priced at "€751"
        And the product "Symfony Con 2017 Bundled Ticket" has an association "Product Bundle" with products "Symfony Con 2017 Conference Ticket" and "Symfony Con 2017 Workshop Ticket"

    @todo
    Scenario: Adding a discount for the bundled products
        When I add product "Symfony Con 2017 Bundled Ticket" to the cart
        Then I should see "Symfony Con 2017 Conference Ticket" with quantity 1 in my cart
        And I should see "Symfony Con 2017 Workshop Ticket" with quantity 1 in my cart
        And my cart total should be "€751.00"
