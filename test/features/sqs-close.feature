Feature: sqs-close
    In order to work with sqs
    As a user
    I want to close a queue

    Background: I have a queue 'myqueue'
        Given I run "sqs init" with "myqueue"

    Scenario: Call 'sqs close myqueue'
        When I run "sqs close" with "myqueue"
        Then it should pass
        When I am in the "var/sqs" path
        Then file "myqueue" should not exist

    Scenario: Call 'sqs close foo' but 'foo' does not exist
        When I run "sqs close" with "foo"
        Then it should fail

    Scenario: Call 'sqs close' without queue
        When I run "sqs close" with " "
        Then it should fail
