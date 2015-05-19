Feature: sqs-list
    In order to use sqs
    As a user
    I want to list a queue

    Background:
        Given I run "sqs init myqueue"
        And I run "sqs add myqueue 'echo task 0'"
        And I run "sqs add myqueue 'echo task 1'"
        And I run "sqs add myqueue 'echo task 2'"
        And I run "sqs add myqueue 'echo task 3'"

    Scenario: Call 'sqs list' should list all queues
        When I run "sqs list"
        Then it should pass
        And the output should contain:
        """
        1 queue(s) in total:
        'myqueue': total 4, exec 0, wait 4, proc 0, host(s) 0
        """

    Scenario: Call 'sqs list' with queue name
        When I run "sqs list myqueue"
        Then it should pass
        And the output should contain:
        """
        'myqueue': total 4, exec 0, wait 4, proc 0, host(s) 0
        """

    Scenario: Call 'sqs list' with queue name
        When I run "sqs list myqueue all"
        Then it should pass
        And the output should contain:
        """
        No processes in queue 'myqueue'
        Queue 'myqueue' is not executing any tasks
        4 task(s) waiting in queue 'myqueue':
        0: echo task 0
        1: echo task 1
        2: echo task 2
        3: echo task 3
        """

    Scenario: Call 'sqs list myqueue wait'
        When I run "sqs list myqueue wait"
        Then it should pass
        And the output should contain:
        """
        4 task(s) waiting in queue 'myqueue':
        0: echo task 0
        1: echo task 1
        2: echo task 2
        3: echo task 3
        """

    Scenario: Call 'sqs list myqueue wait'
        When I run "sqs list myqueue wait first"
        Then it should pass
        And the output should contain:
        """
        0: echo task 0
        """

    Scenario: Call 'sqs list myqueue wait'
        When I run "sqs list myqueue wait last"
        Then it should pass
        And the output should contain:
        """
        3: echo task 3
        """

    Scenario: Call 'sqs list myqueue wait'
        Then I run "sqs list myqueue wait 1 3"
        Then it should pass
        And the output should contain:
        """
        1: echo task 1
        3: echo task 3
        """

    Scenario: Call 'sqs list myqueue exec'
        When I run "sqs list myqueue exec"
        Then it should pass
        And the output should contain:
        """
        Queue 'myqueue' is not executing any tasks
        """

    Scenario: Call 'sqs list myqueue proc'
        When I run "sqs list myqueue proc"
        Then it should pass
        And the output should contain:
        """
        No processes in queue 'myqueue'
        """
