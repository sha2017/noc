## ARP on CumulusLinux

With more than 60 ARPs/s, switchd CPU usage will go over 100% (4 cores) on the tomahawk switches.

The WiFi VLAN hits > 700 ARPs/s..

A solution to this is letting the linux kernel, which doesn't do negative arp caching, talk to arpd before ARPing in broadcast.


Added /usr/sbin/arpd and /usr/lib/x86_64-linux-gnu/libdb5.3.so from debian's iproute2 package and this init script:

<pre>
#!/bin/bash
### BEGIN INIT INFO
# Provides:          arpd
# Required-Start:    $syslog $network
# Required-Stop:     $syslog $network
# Should-Start:
# Should-Stop:
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: ARP daemon
# Description:       ARP daemon
### END INIT INFO

DAEMON="/usr/sbin/arpd"
DAEMON_ARGS="-a 3 -n 900 -p 1 -k -R 40"
INTERFACES=""

set -e

test -x $DAEMON || exit 0

. /lib/lsb/init-functions

[ -r /etc/default/arpd ] && . /etc/default/arpd

start() {
        echo 32768 > /proc/sys/net/ipv4/neigh/default/gc_thresh3
        echo 24576 > /proc/sys/net/ipv4/neigh/default/gc_thresh2
        echo 16384 > /proc/sys/net/ipv4/neigh/default/gc_thresh1

        start-stop-daemon --start --exec $DAEMON -- $DAEMON_ARGS $INTERFACES
        for iface in $INTERFACES;do
                echo 10 > /proc/sys/net/ipv4/neigh/$iface/app_solicit
                echo 0 > /proc/sys/net/ipv4/neigh/$iface/mcast_solicit
        done

}
stop() {
        for iface in $INTERFACES;do
                echo 0 > /proc/sys/net/ipv4/neigh/$iface/app_solicit
                echo 3 > /proc/sys/net/ipv4/neigh/$iface/mcast_solicit
        done
        start-stop-daemon --stop --exec $DAEMON
        sleep 1
}
case $1 in
start)
        start
        ;;
stop)
        stop
        ;;
restart)
        stop
        start
        ;;
reload)
        killall -HUP arpd
        ;;
esac
</pre>

With this /etc/default/arpd:

<pre>
# -k suppress kernel
# -a 3 tries before DEAD
# -n 900 seconds negative cache (8192*3/900=28 ARP/s)
# -R 30 ARPs/sec steady stream for updating
# -B 3 packet burst (not useful, also default)
DAEMON_ARGS="-a 3 -n 900 -p 1 -k -R 30"
INTERFACES="vlan1192 vlan1192-v0"
</pre>