Feature: The fixtures have been set
  In order to execute tests using a database and a set of data
  the fixtures have to be set

  Scenario: Checking the database fixtures
    When I list lines in the entity table
    Then I should see 14 records
