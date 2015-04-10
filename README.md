INSTALLATION
------------

To install SQS, first run the configure script. This configures SQS for the current
setup. The script defines the install location for the executables and the location
of the queue information files. The default install location prefix is set to
'/usr/local' resulting in '/usr/local/bin' for the executables and in
'usr/local/var/sqs' for the queue information.

The install location can be modified via parameters. Call './configure --help' for
more information.

After configuration, issue 'make install' to copy the files to their place. SQS is
then ready to use.


USAGE
-----

SQS can be used to queue task on a single or on multiple hosts. If used on multiple
hosts, the configured directories (see above) have to be accessible under the same
path for all hosts! As a rule of thumb, absolute path names are preferable to
relative path names.

* Single machine examples:

sqs --call <call> [TARGETS]
    Create a queue, run a single process.

sqs --nproc 4 --call <call> [TARGETS]
    Create a queue, run 4 parallel processes.

sqs --nproc 4 --nice 30 --call <call>
    Create a queue, run 4 parallel processes with nice 30.

* Multi-machine examples:

sqs --host 3:30 --call <call> [TARGETS]
    Same as the last single machine example

sqs --host 3:30 --host <host2>:3:40 --call <call> [TARGETS]
    Create a queue, run 4 parallel processes on current host with nice 30 and run 3
    parallel processes on 'host2' with nice 40.

sqs --nproc 4 --host <host2>:3 --call <call> [TARGETS]
    Create a queue, run 4 parallel processes on current host with nice 0 and run 3
    parallel processes on 'host2', also with nice 0.

