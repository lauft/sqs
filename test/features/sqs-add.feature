Feature: sqs-add
    In order to use sqs
    As a user
    I want to add jobs to the queue

    Background: Initialize a queue
        Given I run "sqs init myqueue"

    Scenario: Call 'sqs add myqueue task'
        When I run "sqs add myqueue 'echo task 1'"
        Then it should pass
        When I run "sqs add myqueue 'echo task 2'"
        Then it should pass
        When I change to directory "/var/sqs/myqueue"
        Then directory "lockfile" does not exist
        And I am in directory "/var/sqs/myqueue/wait"
        Then file "0" should exist
        And file "1" should exist

    Scenario: sqs-add should fail when only called with queue
        When I run "sqs add myqueue"
        Then it should fail

    Scenario: sqs-add should fail when called without arguments
        When I run "sqs add"
        Then it should fail
