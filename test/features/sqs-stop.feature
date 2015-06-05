Feature: sqs-stop
    In order to use sqs
    As a user
    I want to stop queue processing

    Background: queue with a task
        Given I run "sqs init myqueue"
        And I run "sqs add 'echo Hello World'"

    Scenario: Call 'sqs stop' without queue
        When I run "sqs start"
        Then it should fail

    Scenario: Call 'sqs stop' on existing queue
        When I run "sqs stop myqueue"
        Then it should pass
        When I change to directory "/var/sqs/myqueue/info"
        Then file "state" should exist
        And "state" file should contain:
        """
        halt
        """
