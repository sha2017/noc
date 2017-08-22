DHCP relay on CumulusLinux 2.5 can only do either IPv4 or IPv6.

It can only have one DHCP relay instance. These scripts fix that.

## IPv4
/etc/default/isc-dhcp-relay

<pre>
# Defaults for isc-dhcp-relay initscript
# sourced by /etc/init.d/isc-dhcp-relay
# installed at /etc/default/isc-dhcp-relay by the maintainer scripts
# SERVERS = Which authoritative DHCP servers
# INTERFACES = Where to relay from
# OPTIONS = dhcrelay extra arguments


SERVERS[0]="145.116.210.5 145.116.210.6"
INTERFACES[0]="vlan1030"
OPTIONS[0]=""

SERVERS[1]="145.116.210.134"
INTERFACES[1]="vlan3130"
OPTIONS[1]=""

</pre>

/etc/init.d/isc-dhcp-relay

<pre>
#!/bin/bash
#
#
### BEGIN INIT INFO
# Provides:          isc-dhcp-relay
# Required-Start:    $remote_fs $network
# Required-Stop:     $remote_fs $network
# Should-Start:      $local_fs $routing
# Should-Stop:       $local_fs $routing
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: DHCP relay
# Description:       Dynamic Host Configuration Protocol Relay
### END INIT INFO

# It is not safe to start if we don't have a default configuration...
if [ ! -f /etc/default/isc-dhcp-relay ]; then
        echo "/etc/default/isc-dhcp-relay does not exist! - Aborting..."
        echo "Run 'dpkg-reconfigure isc-dhcp-relay' to fix the problem."
        exit 1
fi

# Read init script configuration (interfaces the daemon should listen on
# and the DHCP server we should forward requests to.)
[ -f /etc/default/isc-dhcp-relay ] && . /etc/default/isc-dhcp-relay

set -x
for i in $(seq 0 $((${#SERVERS[@]}-1))); do
        CUR_SERVERS="${SERVERS[$i]}"
        CUR_INTERFACES="${INTERFACES[i]}"
        CUR_OPTIONS="${OPTIONS[i]}"
        # Build command line for interfaces (will be passed to dhrelay below.)
        IFCMD=""
        if test "$CUR_INTERFACES" != ""; then
                for I in $CUR_INTERFACES; do
                        IFCMD=${IFCMD}"-i "${I}" "
                done
        fi

        DHCRELAYPID=/var/run/dhcrelay.${i}.pid
        export PATH_DHCRELAY_PID="$DHCRELAYPID"

        case "$1" in
                start)
                        # If cl-mgmtvrf is enabled, preload the sockmark library to mark the
                        # sockets to use main table instead of mgmt table.
                        PRELOADLIB=/usr/lib/sockmark.so
                        CLMGMTVRF=/usr/sbin/cl-mgmtvrf
                        if [ -f $CLMGMTVRF ] && [ -f $PRELOADLIB ] && service cl-mgmtvrf status | grep -q "enabled" ;
                        then
                                LD_PRELOAD=$PRELOADLIB start-stop-daemon --start --quiet --pidfile $DHCRELAYPID \
                                        --exec /usr/sbin/dhcrelay -- $CUR_OPTIONS -q $IFCMD $CUR_SERVERS
                        else
                                start-stop-daemon --start --quiet --pidfile $DHCRELAYPID \
                                        --exec /usr/sbin/dhcrelay -- $CUR_OPTIONS -q $IFCMD $CUR_SERVERS
                        fi
                        ;;
                stop)
                        start-stop-daemon --stop --quiet --pidfile $DHCRELAYPID
                        ;;
                restart | force-reload)
                        $0 stop
                        sleep 2
                        $0 start
                        ;;
                *)
                        echo "Usage: /etc/init.d/isc-dhcp-relay {start|stop|restart|force-reload}"
                        exit 1
        esac
done
exit 0
</pre>

## IPv6
Alright. IPv6 relay does not work with isc-dhcp-relay, wide-dhcpv6-relay or dibbler in any kind of sane setup (routed DHCP packets is a weird concept apparently, relaying between links not so much.. apparently).


