Feature: sqs-remove
    In order to use sqs
    As a user
    I want to remove waiting jobs from the queue

    Background:
        Given I run "sqs init myqueue"
        And I run "sqs add myqueue 'echo Hello World'"
        And I run "sqs add myqueue 'echo Hello World'"
        And I run "sqs add myqueue 'echo Hello World'"
        And I am in directory "/var/sqs/myqueue/wait"
        And file "0" exists
        And file "1" exists
        And file "2" exists
        And I am in directory "/var/sqs/myqueue"
        And directory "lockfile" does not exist
        And I am in directory "/var/sqs/myqueue/wait"

    Scenario: Call 'sqs remove myqueue id'
        When I run "sqs remove myqueue 0"
        Then it should pass
        Then file "0" should not exist
        And file "1" should exist
        And file "2" should exist

    Scenario: Call 'sqs remove myqueue first'
        When I run "sqs remove myqueue first"
        Then it should pass
        Then file "0" should not exist
        And file "1" should exist
        And file "2" should exist

    Scenario: Call 'sqs remove myqueue last'
        When I run "sqs remove myqueue last"
        Then it should pass
        Then file "0" should exist
        And file "1" should exist
        And file "2" should not exist

    Scenario: Call 'sqs remove myqueue all'
        When I run "sqs remove myqueue all"
        Then it should pass
        Then file "0" should not exist
        And file "1" should not exist
        And file "2" should not exist

    Scenario: Call 'sqs remove myqueue id' with wrong id
        When I run "sqs remove myqueue 3"
        Then it should fail
        And file "0" should exist
        And file "1" should exist
        And file "2" should exist

    Scenario: Call 'sqs remove myqueue'
        When I run "sqs remove myqueue"
        Then it should fail

    Scenario: Call 'sqs remove'
        When I run "sqs remove"
        Then it should fail
