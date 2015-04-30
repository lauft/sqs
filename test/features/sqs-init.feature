Feature: sqs-init
    In order to use sqs
    As a user
    I want to initialise a queue

    Scenario: Call 'sqs init' with queue name
        When I run "sqs init" with "myqueue"
        Then it should pass
        When I am in the "var/sqs" path
        Then file "myqueue" should exist
        When I am in the "myqueue" path
        Then directory "info" exists
        When I am in the "info" path
        Then file "state" exists

    Scenario: Call 'sqs init' without queue name
        When I run "sqs init" with " "
        Then it should fail
