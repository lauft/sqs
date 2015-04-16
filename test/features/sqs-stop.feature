Feature: sqs-stop
    In order to use sqs
    As a user
    I want to stop queue processing

    Background: queue with a task
        Given I run "sqs init" with "myqueue"
        And I run "sqs add" with "'echo Hello World'"

    Scenario: Call 'sqs stop' without queue
        When I run "sqs start" with " "
        Then it should fail

    Scenario: Call 'sqs stop' on existing queue
        When I run "sqs stop" with "myqueue"
        Then it should pass
