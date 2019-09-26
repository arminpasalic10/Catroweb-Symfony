@homepage
Feature: A button exists on every program page, which should allow the user to steal the program

  Background:
    Given there are users:
      | name     | password | token      | email               | id |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | User2    | 654321   | cccccccccc | dev2@pocketcode.org |  2 |
      | User3    | 654321   | cccccccccc | dev3@pocketcode.org |  3 |
      | User4    | 654321   | cccccccccc | dev4@pocketcode.org |  4 |
    And there are programs:
      | id | name       | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | oldestProg | p1          | Catrobat | 3         | 2             | 12    | 01.01.2009 12:00 | 0.8.5   |
      | 2  | program 02 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 03 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | program 04 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | program 05 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | program 06 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 7  | program 07 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 8  | program 08 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9  | program 09 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10 | program 10 |             | User2    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |

    Scenario: on every program page, a steal button should always be presents
      Given I log in as "Catrobat" with the password "123456"
      And I am on "/app/project/2"
      And the element "#steal-program" should be visible

    Scenario: user clicks the button and steals the program
      Given I log in as "User3" with the password "654321"
      And I am on "/app/project/2"
      When I click "#steal-program"
      Then I wait 20 milliseconds
      When I am on "/app/user"
      Then I should see 1 "#myprofile-programs .program"
      And I should see "program 02"
      



