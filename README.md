SQS
===

SQS is a set of bash scripts which help you queue tasks. You can have them executed
one by one or have tasks executed in parallel. SQS offers a command line interface 
to create, start, stop, and control the queue, as well as monitor queue status.

INSTALLATION
------------

To install SQS, first run the configure script. This configures SQS for the current
setup. The script defines the install location for the executables and the location
of the queue information files. The default install location prefix is set to
`${HOME}/.local` resulting in `${HOME}/.local/bin` for the executables and in
`${HOME}/.local/var/sqs` for the queue information.

The install location can be modified via parameters. Call `./configure --help` for
more information.

After configuration, issue `make install` to copy the files to their place.
 
SQS is now ready to use.

USAGE
-----

SQS processes jobs via queues. To create a queue call

    sqs init <queue>

where `<queue>` is the name of your queue.

To add tasks to a queue, issue

    sqs add <queue> <task>

where `<task>` is the command you want to have executed.

After filling a queue with tasks, you can start processing by

    sqs start <queue>

You can add task at any time by `sqs add <queue> <task>`.

If you want to halt your queue call

    sqs stop <queue>

The queue then stops processing new tasks. Task currently running are not affected.
