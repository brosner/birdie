#!/bin/bash
APPNAME=birdie
PIDFILE=/home/birdie/webapps/birdie/logs/${APPNAME}.pid
HOST=127.0.0.1
PORT=3033
# Maximum requests for a child to service before expiring
MAXREQ=500
# Spawning method - prefork or threaded
METHOD=prefork
# Maximum number of children to have idle
MAXSPARE=3
# Minimum number of children to have idle
MINSPARE=1
# Maximum number of children to spawn
MAXCHILDREN=2

# cd "`dirname $0`"

function failure {
    STATUS=$?;
    echo; echo "failed $1 (exit code ${STATUS}).";
    exit ${STATUS};
}

function start_server {
    python manage.py runfcgi host=$HOST port=$PORT pidfile=$PIDFILE \
        ${MAXREQ:+maxrequests=$MAXREQ} \
        ${METHOD:+method=$METHOD} \
        ${MAXSPARE:+maxspare=$MAXSPARE} \
        ${MINSPARE:+minspare=$MINSPARE} \
        ${MAXCHILDREN:+maxchildren=$MAXCHILDREN} \
        ${DAEMONIZE:+damonize=True}
}

function stop_server {
    kill `cat $PIDFILE` || failure "stopping ${APPNAME}"
    rm $PIDFILE
}

DAEMONIZE=$2

case "$1" in
    start)
        echo -n "starting ${APPNAME}: "
        [ -e $PIDFILE ] && { echo "PID file exists."; exit; }
        start_server || failure "starting ${APPNAME}"
        echo "done."
        ;;
    stop)
        echo -n "stopping ${APPNAME}: "
        [ -e $PIDFILE ] || { echo "no PID file found."; exit; }
        stop_server
        echo "done."
        ;;
    poll)
        [ -e $PIDFILE ] && exit;
        start_server || failure "starting ${APPNAME}"
        ;;
    restart)
        echo -n "restarting ${APPNAME}: "
        [ -e $PIDFILE ] || { echo -n "no PID file found..."; }
        stop_server
        start_server || failure "restarting ${APPNAME}"
        echo "done."
        ;;
    *)
        echo "usage: $0 {start|stop|restart} [--daemonize]"
        ;;
esac

exit 0
