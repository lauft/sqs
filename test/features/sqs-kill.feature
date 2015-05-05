Feature: sqs-kill
    In order to use sqs
    As a user
    I want to kill processes from the queue

    Background:
        Given I run "sqs init" with "myqueue"
        And I run "sqs add" with "myqueue sqstesttask -1"
        And I run "sqs add" with "myqueue sqstesttask -1"
        And I run "sqs add" with "myqueue sqstesttask -1"
        And I run "sqs start" with "myqueue >/dev/null 2>&1"
        And 3 jobs "sqstesttask" are running

    Scenario: Call 'sqs kill myqueue id'
        When I run "sqs kill" with "myqueue 0"
        Then it should pass
        And 2 jobs "sqstesttask" are running

    Scenario: Call 'sqs kill myqueue first'
        When I run "sqs kill" with "myqueue first"
        Then it should pass
        And 2 jobs "sqstesttask" are running

    Scenario: Call 'sqs kill myqueue last'
        When I run "sqs kill" with "myqueue last"
        Then it should pass
        And 2 jobs "sqstesttask" are running

    Scenario: Call 'sqs kill myqueue all'
        When I run "sqs kill" with "myqueue all"
        Then it should pass
        And 0 jobs "sqstesttask" are running

    Scenario: Call 'sqs kill myqueue id' with wrong id
        When I run "sqs kill" with "myqueue 100"
        Then it should fail
        And 3 jobs "sqstesttask" are running

    Scenario: Call 'sqs kill myqueue'
        When I run "sqs kill" with "myqueue"
        Then it should fail
        And 3 jobs "sqstesttask" are running

    Scenario: Call 'sqs kill'
        When I run "sqs kill" with " "
        Then it should fail
        And 3 jobs "sqstesttask" are running
