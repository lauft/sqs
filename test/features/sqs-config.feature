Feature: sqs-config
    In order to use sqs
  As a user
  I want to set and get config values

  Scenario: Call 'sqs config set KEY VAL'
    Given I am in the "bin" path
    And there is a file named "sqsconf" with:
    """
    """
    When I run "sqs config" with "set KEY VAL"
    Then it should pass
    And there should be a file named "sqsconf" with:
    """
    KEY=VAL

    """

  Scenario: Call 'sqs config set KEY VAL' overwrites existing entry
    Given I am in the "bin" path
    And there is a file named "sqsconf" with:
    """
    KEY=FOO
    """
    When I run "sqs config" with "set KEY VAL"
    Then it should pass
    And there should be a file named "sqsconf" with:
    """
    KEY=VAL

    """

  Scenario: Call 'sqs config get KEY DEF'
    Given I am in the "bin" path
    And there is a file named "sqsconf" with:
    """
    KEY=VAL
    """
    When I run "sqs config" with "get KEY DEF"
    Then it should pass
    And the output should contain:
    """
    VAL
    """

  Scenario: Call 'sqs config get KEY DEF', but KEY does not exist
    Given I am in the "bin" path
    And there is a file named "sqsconf" with:
    """
    """
    When I run "sqs config" with "get KEY DEF"
    Then it should pass
    And the output should contain:
    """
    DEF
    """

  Scenario: Call 'sqs config get KEY' without supplying default value
    When I run "sqs config" with "get KEY"
    Then it should fail

  Scenario: Call 'sqs config set KEY' without supplying a value
    When I run "sqs config" with "set KEY"
    Then it should fail

  Scenario: Call 'sqs config get'
    When I run "sqs config" with "get"
    Then it should fail

  Scenario: Call 'sqs config set'
    When I run "sqs config" with "set"
    Then it should fail

  Scenario: Call 'sqs config'
    When I run "sqs config" with " "
    Then it should fail
