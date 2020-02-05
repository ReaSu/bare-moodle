@local @local_randomiser
Feature: In order to chose a number of random students from a group, as an editingteacher
  i need to get students from just one group.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher  | tea       | cher     | teacher@example.com  |
      | student1 | student   | one      | student1@example.com |
      | student2 | student   | two      | student2@example.com |
      | student3 | student   | three    | student3@example.com |
      | student4 | student   | four     | student4@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher  | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
    And the following "groups" exist:
      | name | course | idnumber |
      | group1 | C1   | G1       |
    And the following "group members" exist:
      | user    | group |
      |student1 | G1    |
      |student3 | G1    |
    And I log in as "teacher"
    And I am on "Course 1" course homepage
    And I navigate to course participants
    And I follow "Randomiser"

  Scenario: Groups and "All" are shown in the select box
    Then the "id_groups" select box should contain "All, group1"

  Scenario: Participants who are not in the group should not be selected
    Given I set the following fields to these values:
      | amount    | 1      |
      | id_groups | group1 |
    And I press "submitbutton"
    Then I should not see "student2"
    And I should not see "student 4"

  Scenario: Teachers should not be selected
    Given I set the following fields to these values:
      | amount    | 6   |
      | id_groups | All |
    And I press "Pick users"
    Then I should see "student1"
    And I should see "student2"
    And I should see "student3"
    And I should see "student4"
    And I should not see "teacher"

  Scenario: the correct number of students is selected
    Given I set the following fields to these values:
      | amount    | 2      |
      | id_groups | group1 |
    And I press "Pick users"
    Then I should see "student1"
    And I should see "student3"
    And I should not see "student2"
    And I should not see "student4"
    When I set the following fields to these values:
      | amount    | 1      |
      | id_groups | group1 |

