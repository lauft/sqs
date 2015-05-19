Feature: sqs
    In order to use sqs
    As a user
    I want to have a command line interface

    Scenario: Call 'sqs' without parameters should display minimal help
        When I run "sqs"
        Then it should fail
        And the output should contain:
        """
        SQS, a simple queuing system.

        Usage: sqs <command> [<options/arguments>]

        Call 'sqs help' for a list of available commands
        Call 'sqs help <command>' for details about a specific command
        """

    Scenario: Call 'sqs' with non-existing command
        When I run "sqs foo"
        Then it should fail
