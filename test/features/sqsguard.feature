Feature: sqsguard
    In order to prevent race conditions
    As a sqs command
    I want to lock sqs directories

    Background: I have a queue
        Given I run "sqs init myqueue"

    Scenario: Call 'sqsguard'
        When I run "sqsguard"
        Then it should fail

    Scenario: Call 'sqsguard lock'
        When I run "sqsguard lock"
        Then it should fail

    Scenario: Call 'sqsguard lock myqueue'
        When I run "sqsguard lock myqueue"
        Then it should fail

    Scenario: Call 'sqsguard lock myqueue token'
        Given I am in directory "var/sqs"
        And directory "myqueue" exists
        And directory "lockfile" does not exist
        When  I run "sqsguard lock myqueue sqstoken"
        Then it should pass
        When I change to directory "/var/sqs/myqueue"
        Then directory "lockfile" should exist

    Scenario: Try to lock already locked queue
        Given I am in directory "/var/sqs"
        And directory "myqueue" exists
        And directory "lockfile" does not exist
        And I run "sqsguard lock myqueue sqstoken"
        When I run "sqsguard lock myqueue token 1"
        Then it should fail

    Scenario: Call 'sqsguard unlock'
        When I run "sqsguard unlock"
        Then it should fail

    Scenario: Call 'sqsguard unlock myqueue'
        When I run "sqsguard unlock myqueue"
        Then it should fail

    Scenario: Unlock queue with wrong token
        Given I am in directory "/var/sqs"
        And directory "myqueue" exists
        And directory "lockfile" does not exist
        And I run "sqsguard lock myqueue sqstoken"
        When I run "sqsguard unlock myqueue foo"
        Then it should fail

    Scenario: Unlock queue with correct token
        Given I am in directory "/var/sqs"
        And directory "myqueue" exists
        And directory "lockfile" does not exist
        And I run "sqsguard lock myqueue sqstoken"
        When I run "sqsguard unlock myqueue sqstoken"
        Then it should pass
        And directory "lockfile" does not exist
