Feature: sqs-add
    In order to use sqs
    As a user
    I want to add jobs to the queue

    Background:
        Given I run "sqs init" with "myqueue"

    Scenario: Call 'sqs add myqueue task'
        When I run "sqs add" with "myqueue 'echo Hello World'"
        Then it should pass
        When I am in the "var/sqs/myqueue" path
        Then directory "lockfile" does not exist
        And I am in the "wait" path
        Then file "0" should exist


    Scenario: Call 'sqs add myqueue'
        When I run "sqs add" with "myqueue"
        Then it should fail

    Scenario: Call 'sqs add'
        When I run "sqs add" with " "
        Then it should fail
