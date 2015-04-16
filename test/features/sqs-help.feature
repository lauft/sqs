Feature: sqs-help
    In order to use sqs
    As a user
    I want to read help on commands

    Scenario: Call 'sqs help'
        When I run "sqs" with "help"
        Then it should pass
        And the output should match:
        """
        available commands:
        ( - .+\.\n?)+
        """

    Scenario Outline: Call 'sqs help' with commands should display man page
        When I run "sqs help" with <cmd>
        Then it should pass
        And the output should match:
        """
        NAME
        (.*\n)*
        SYNOPSIS
        (.*\n)*
        DESCRIPTION
        (.*\n)*
        AUTHOR
        (.*\n)*
        SQS
               Part of the sqs suite
        """

    Examples:
        | cmd     |
        | add     |
        | close   |
        | config  |
        | deploy  |
        | dismiss |
        | get     |
        | getopt  |
        | help    |
        | init    |
        | set     |
        | setopt  |
        | start   |
        | stop    |
