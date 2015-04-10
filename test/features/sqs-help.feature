Feature: sqs-help
    In order to use sqs
    As a user
    I want to read help on commands

    Scenario: Call 'sqs help'
        When I run "sqs" with "help"
        Then it should pass
        And the output should contain:
        """
        available commands:
         -
        """

    Scenario: Call 'sqs help init'
        When I run "sqs help" with "init"
        Then it should pass
        And the output should contain:
        """
        SYNOPSIS
        """