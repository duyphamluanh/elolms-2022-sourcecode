@block @block_elo_reminder_users
Feature: The elo reminder users block allow you to see who is currently online on frontpage
  In order to enable the elo reminder users block on the front page page
  As an admin
  I can add the elo reminder users block to the front page page

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |

  Scenario: View the elo reminder users block on the front page and see myself
    Given I log in as "admin"
    And I am on site homepage
    And I navigate to "Turn editing on" in current page administration
    When I add the "Elo Reminder users" block
    Then I should see "Admin User" in the "Elo Reminder users" "block"
    And I should see "1 online user" in the "Elo Reminder users" "block"

  Scenario: View the elo reminder users block on the front page as a logged in user
    Given I log in as "admin"
    And I am on site homepage
    And I navigate to "Turn editing on" in current page administration
    And I add the "Elo Reminder users" block
    And I log out
    And I log in as "student2"
    And I log out
    When I log in as "student1"
    And I am on site homepage
    Then I should see "Admin User" in the "Elo Reminder users" "block"
    And I should see "Student 1" in the "Elo Reminder users" "block"
    And I should see "Student 2" in the "Elo Reminder users" "block"
    And I should see "3 elo reminder users" in the "Elo Reminder users" "block"

  Scenario: View the elo reminder users block on the front page as a guest
    Given I log in as "admin"
    And I am on site homepage
    And I navigate to "Turn editing on" in current page administration
    And I add the "Elo Reminder users" block
    And I log out
    And I log in as "student2"
    And I log out
    And I log in as "student1"
    And I log out
    When I log in as "guest"
    And I am on site homepage
    Then I should see "Admin User" in the "Elo Reminder users" "block"
    And I should see "Student 1" in the "Elo Reminder users" "block"
    And I should see "Student 2" in the "Elo Reminder users" "block"
    And I should see "3 elo reminder users" in the "Elo Reminder users" "block"

  @javascript
  Scenario: Hide/show user's online status from/to other users in the elo reminder users block on front page
    Given I log in as "admin"
    And I am on site homepage
    And I navigate to "Turn editing on" in current page administration
    And I add the "Elo Reminder users" block
    And I log out
    When I log in as "student1"
    And I am on site homepage
    Then "Hide" "icon" should exist in the "#change-user-visibility" "css_element"
    When I click on "#change-user-visibility" "css_element"
    And I wait "1" seconds
    Then "Show" "icon" should exist in the "#change-user-visibility" "css_element"
    And I log out
    When I log in as "student2"
    And I am on site homepage
    Then I should see "2 online user" in the "Elo Reminder users" "block"
    And I should see "Admin" in the "Elo Reminder users" "block"
    And I should see "Student 2" in the "Elo Reminder users" "block"
    And I should not see "Student 1" in the "Elo Reminder users" "block"
    And I log out
    When I log in as "student1"
    And I am on site homepage
    Then "Show" "icon" should exist in the "#change-user-visibility" "css_element"
    When I click on "#change-user-visibility" "css_element"
    And I wait "1" seconds
    Then "Hide" "icon" should exist in the "#change-user-visibility" "css_element"
    And I log out
    When I log in as "student2"
    And I am on site homepage
    Then I should see "3 elo reminder users" in the "Elo Reminder users" "block"
    And I should see "Admin" in the "Elo Reminder users" "block"
    And I should see "Student 2" in the "Elo Reminder users" "block"
    And I should see "Student 1" in the "Elo Reminder users" "block"
