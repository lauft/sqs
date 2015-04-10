Feature: sqs-start
    In order to use sqs
    As a user
    I want to start queue processing

    Background: queue with a task
        Given I run "sqs init" with "myqueue"
        And I run "sqs add" with "'echo Hello World'"

    Scenario: Call 'sqs start' without queue
        When I run "sqs start" with " "
        Then it should fail

    Scenario: Call 'sqs start' empties queue
        When I run "sqs start" with "myqueue"
        Then it should pass
