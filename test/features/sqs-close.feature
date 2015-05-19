Feature: sqs-close
    In order to use sqs
    As a user
    I want to close a queue

    Background: I have a queue 'myqueue'
        Given I run "sqs init myqueue"

    Scenario: Call 'sqs close myqueue'
        When I run "sqs close myqueue"
        Then it should pass
        When I change to directory "var/sqs"
        Then file "myqueue" should not exist

    Scenario: Call 'sqs close foo' but 'foo' does not exist
        When I run "sqs close foo"
        Then it should fail

    Scenario: Call 'sqs close' without queue
        When I run "sqs close"
        Then it should fail
