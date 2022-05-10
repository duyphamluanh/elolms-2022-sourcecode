@block @block_elo_reminder_users
Feature: The elo reminder users block allow you to see who is currently online
  In order to enable the elo reminder users block on an course page
  As a teacher
  I can add the elo reminder users block to a course page

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |

    And the following "course enrolments" exist:
      | user | course | role           |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student        |

  Scenario: Add the elo reminder users on course page and see myself
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    When I add the "Elo Reminder users" block
    Then I should see "Teacher 1" in the "Elo Reminder users" "block"
    And I should see "1 online user" in the "Elo Reminder users" "block"

  Scenario: Add the elo reminder users on course page and see other logged in users
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Elo Reminder users" block
    And I log out
    And I log in as "student2"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then I should see "Teacher 1" in the "Elo Reminder users" "block"
    And I should see "Student 1" in the "Elo Reminder users" "block"
    And I should not see "Student 2" in the "Elo Reminder users" "block"
    And I should see "2 elo reminder users" in the "Elo Reminder users" "block"

  @javascript
  Scenario: Hide/show user's online status from/to other users in the elo reminder users block on course page
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Elo Reminder users" block
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then "Hide" "icon" should exist in the "#change-user-visibility" "css_element"
    When I click on "#change-user-visibility" "css_element"
    And I wait "1" seconds
    Then "Show" "icon" should exist in the "#change-user-visibility" "css_element"
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then I should see "1 online user" in the "Elo Reminder users" "block"
    And I should see "Teacher 1" in the "Elo Reminder users" "block"
    And I should not see "Student 1" in the "Elo Reminder users" "block"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    Then "Show" "icon" should exist in the "#change-user-visibility" "css_element"
    When I click on "#change-user-visibility" "css_element"
    And I wait "1" seconds
    Then "Hide" "icon" should exist in the "#change-user-visibility" "css_element"
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then I should see "2 elo reminder users" in the "Elo Reminder users" "block"
    And I should see "Teacher 1" in the "Elo Reminder users" "block"
    And I should see "Student 1" in the "Elo Reminder users" "block"
