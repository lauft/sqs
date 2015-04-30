Feature: sqs-remove
    In order to use sqs
    As a user
    I want to remove waiting jobs from the queue

    Background:
        Given I run "sqs init" with "myqueue"
        And I run "sqs add" with "myqueue 'echo Hello World'"
        And I am in the "var/sqs/myqueue/wait" path
        And file "0" exists
        And I am in the "../." path
        And directory "lockfile" does not exist

    Scenario: Call 'sqs remove myqueue id'
        When I run "sqs remove" with "myqueue 0"
        Then it should pass
        Then file "0" should not exist

    Scenario: Call 'sqs remove myqueue id' with wrong id
        When I run "sqs remove" with "myqueue 1"
        Then it should fail
        And I am in the "wait" path
        And file "0" should exist

    Scenario: Call 'sqs remove myqueue'
        When I run "sqs remove" with "myqueue"
        Then it should fail

    Scenario: Call 'sqs remove'
        When I run "sqs remove" with " "
        Then it should fail
