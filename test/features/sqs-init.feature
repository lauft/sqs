Feature: sqs-init
    In order to use sqs
    As a user
    I want to initialise a queue

    Scenario: Call 'sqs init' with queue name
        When I run "sqs init myqueue"
        Then it should pass
        When I change to directory "/var/sqs"
        Then file "myqueue" should exist
        When I change to directory "/var/sqs/myqueue"
        Then directory "info" exists
        When I change to directory "/var/sqs/myqueue/info"
        Then file "state" exists

    Scenario: Call 'sqs init' without queue name
        When I run "sqs init"
        Then it should fail
