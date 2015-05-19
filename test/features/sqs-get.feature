Feature: sqs-get
    In order to query queue settings
    As a sqs command
    I want to get host settings from a queue info file

    Background: I have a queue
        Given I run "sqs init" with "myqueue"

    Scenario: Call 'sqs-get FILE HOST DEFAULT' reads an existing setting
        When I am in directory "var/sqs/myqueue/info"
        And I run "sqs-get" with "nproc $(hostname) 0"
        Then it should pass
        And the output should contain:
        """
        8
        """

    Scenario: Call 'sqs-get FILE HOST DEFAULT' falls back to default
        When I am in directory "var/sqs/myqueue/info"
        And I run "sqs-get" with "nproc nohost 42"
        Then it should pass
        And the output should contain:
        """
        42
        """

    Scenario: Call 'sqs-get' with nonexistent file
        When I run "sqs-get" with "var/sqs/nope/info/nproc myhost 0"
        Then it should fail
