Feature: sqs-start
    In order to use sqs
    As a user
    I want to start queue processing

    Background: queue with a task
        Given I run "sqs init myqueue"
        And I run "sqs add 'echo Hello World'"

    Scenario: Call 'sqs start' without queue
        When I run "sqs start  "
        Then it should fail

    Scenario: Call 'sqs start' empties queue
        When I run "sqs start myqueue"
        Then it should pass
        When I change to directory "/var/sqs/myqueue/info"
        Then file "state" should exist
        And "state" file should contain:
        """
        run
        """
        When I change to directory "/var/sqs/myqueue/wait"
        Then file "0" should not exist
