Feature: sqs-remove
    In order to use sqs
    As a user
    I want to remove waiting jobs from the queue

    Background:
        Given I run "sqs init" with "myqueue"
        And I run "sqs add" with "myqueue 'echo Hello World'"
        And I run "sqs add" with "myqueue 'echo Hello World'"
        And I run "sqs add" with "myqueue 'echo Hello World'"
        And I am in the "var/sqs/myqueue/wait" path
        And file "0" exists
        And file "1" exists
        And file "2" exists
        And I am in the "../." path
        And directory "lockfile" does not exist
        And I am in the "wait" path

    Scenario: Call 'sqs remove myqueue id'
        When I run "sqs remove" with "myqueue 0"
        Then it should pass
        Then file "0" should not exist
        And file "1" should exist
        And file "2" should exist

    Scenario: Call 'sqs remove myqueue first'
        When I run "sqs remove" with "myqueue first"
        Then it should pass
        Then file "0" should not exist
        And file "1" should exist
        And file "2" should exist

    Scenario: Call 'sqs remove myqueue last'
        When I run "sqs remove" with "myqueue last"
        Then it should pass
        Then file "0" should exist
        And file "1" should exist
        And file "2" should not exist

    Scenario: Call 'sqs remove myqueue all'
        When I run "sqs remove" with "myqueue all"
        Then it should pass
        Then file "0" should not exist
        And file "1" should not exist
        And file "2" should not exist

    Scenario: Call 'sqs remove myqueue id' with wrong id
        When I run "sqs remove" with "myqueue 3"
        Then it should fail
        And file "0" should exist
        And file "1" should exist
        And file "2" should exist

    Scenario: Call 'sqs remove myqueue'
        When I run "sqs remove" with "myqueue"
        Then it should fail

    Scenario: Call 'sqs remove'
        When I run "sqs remove" with " "
        Then it should fail
