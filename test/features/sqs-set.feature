Feature: sqs-set
    In order to update queue settings
    As a sqs command
    I want to set host settings in a queue info file

    Background: I have a queue
        Given I run "sqs init" with "myqueue"

    Scenario: Call 'sqs-set FILE HOST VALUE' writes an entry
        When I am in directory "var/sqs/myqueue/info"
        And I run "sqs-set" with "hosts testhost 4"
        Then it should pass
        Then "hosts" file should contain:
        """
        testhost:4
        """

    Scenario: Call 'sqs-set FILE HOST VALUE' overwrites an existing entry
        When I am in directory "var/sqs/myqueue/info"
        And I run "sqs-set" with "hosts testhost 4"
        And I run "sqs-set" with "hosts testhost 2"
        Then it should pass
        Then "hosts" file should contain:
        """
        testhost:2
        """

    Scenario: Call 'sqs-set' without arguments
        When I run "sqs-set" with " "
        Then it should fail

    Scenario: Call 'sqs-set' with nonexistent file
        When I run "sqs-set" with "var/sqs/nope/info/nproc myhost 5"
        Then it should fail
