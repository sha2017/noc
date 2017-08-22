# Wireless Network SHA2017

## Controllers

| Name | Model | Serial | Role | Max AP's | Max users | Uplink | Location | Comments |
| ---- | ----- | ------ | ---- | -------- | --------- | ------ | -------- | -------- |
| sha2017-mm | VMM | ?? | Mobility Master | - | - | VM | E0 | Virtual Machine |
| sha2017-wlc-e0 | 7210 | ?? | Node | 512 | 16K | 3x 10GBASE-LR | E0 | Cluster member |
| sha2017-wlc-l0 | 7210 | ?? | Node | 512 | 16K | 3x 10GBASE-LR | L0 | Cluster member |

## AP Locations / Inventory / Naming

* [AP-Locations](ap/locations.md)
* AP-Inventory

We're using the VisualRF-tool inside of Aruba AirWave for AP placement/planning. 
AP-type to AP-model conversion:

* AP LD (Low Density - 2*2 MIMO dual radio): AP-205(H), AP-11x, AP-13x
* AP HD (High Density - 3*3 MIMO dual radio): AP-21x, AP-22x, AP-32x
* AP Outdoor: AP-175, AP-275, AP-277, AP-135B (In Box)
* AirMon: AP-11x, AP-13x

AP-naming within controller/AirWave:

* We'll make sure AP's have received an unique label during staging and are provisioned within the controller with this label.
* After deployment AP's will come online with this label as their name.
* AP's should be re-provisioned after deployment. They should renamed using this naming convention:

<pre>$APNAME_$APLABEL</pre>

Example:

<pre>L0-ENTRANCE-1_AP65</pre>

* $APNAME = AP-name as noted in [AP-Locations](ap/locations.md).
* $APLABEL = whatever label is on the AP. This should correspond with AP-Inventory.
AP-names must not exceed 64 chars.

## Network config
This year we'll again be using the Aruba controller-based solution. We're deploying two Aruba 7210 controllers at E0/L0 which are all connected with 2x 10GBASE-LR LACP to the WiFi routers. Accesspoints are deployed in the end-user VLANs and will tunnel all of the traffic to the controller. 

### VLAN's
These VLAN's are to be tagged to the controllers:

* VLAN 800 (mgmt)
* VLAN 1192 (users) 
* VLAN 1255 (ticket scanners)
* VLAN 2215 (users noc WiFi)
* VLAN 2219 (users orga WiFi)
* VLAN 3001 (sysadmin mgmt)
* VLAN 3003 (deco artnet)
* VLAN 3004 (voc/av private)
* VLAN 3005 (deco laser)
* VLAN 3006 (deco soundlevel)

### Special IP's:
* 145.116.209.132 - sha2017-mm Management (Mobility Master VM on sha2017-wifi-kvm2 in e0)
* 145.116.209.133 - sha2017-wlc-e0 Management (7210)
* 145.116.209.134 - sha2017-wlc-l0 Management (7210)
* 145.116.209.135 - sha2017-vip Management (cluster VIP)
* 145.116.210.129 - sha2017-wifi-kvm1
* 145.116.210.130 - sha2017-wifi-kvm2
* 145.116.210.131 - radius-1 (VM on sha2017-wifi-kvm1)
* 145.116.210.132 - radius-2 (VM on one of the NOC DC KVM servers)
* 145.116.210.133 - Aruba AirWave (VM on sha2017-wifi-kvm1)
* 145.116.210.134 - ArtNet (VM on sha2017-wifi-kvm1)

The controllers do not need IP addresses in end-user VLAN's.

### Routing
We'll apply these routes to the controllers:

* 0.0.0.0/0 -> 145.116.209.129 (via mgmt)

### Mgmt ACL / Control plane security
To protect the management of the controller a management ACL is applied to the controller which only allows access from these subnets:

* 145.116.209.0/24
* 145.116.210.0/24
* 145.116.214.0/23

These rules are defined in "firewall-cp" or control plane firewall.

<pre>firewall cp
   ipv4 permit 145.116.209.0 255.255.255.0 proto 6 ports 0 65535
   ipv4 permit 145.116.209.0 255.255.255.0 proto 17 ports 0 65535
   ipv4 permit 145.116.210.0 255.255.255.0 proto 6 ports 0 65535
   ipv4 permit 145.116.210.0 255.255.255.0 proto 17 ports 0 65535
   ipv4 permit 145.116.214.0 255.255.254.0 proto 6 ports 0 65535
   ipv4 permit 145.116.214.0 255.255.254.0 proto 17 ports 0 65535

   ipv4 permit 151.216.0.0 255.255.0.0 proto 6 ports 21 21
   ipv4 permit 151.216.0.0 255.255.0.0 proto 17 ports 0 65535
   ipv4 permit 151.216.0.0 255.255.0.0 proto 47 ports 0 65535

   ipv4 permit 145.116.208.0 255.255.240.0 proto 6 ports 21 21
   ipv4 permit 145.116.208.0 255.255.240.0 proto 17 ports 0 65535
   ipv4 permit 145.116.208.0 255.255.240.0 proto 47 ports 0 65535

   ipv4 deny any proto 6 ports 0 65535
   ipv4 deny any proto 47 ports 0 65535
!</pre>

AP's can reach the controller in the above noted subnets.

AP's must always be initially provisioned in one of the management subnets.
Control plane security is enabled on the controller which requires the AP's to build an IPSec tunnel for control traffic. 
These IPSec tunnels are set up using X509 certificates, certificate auto-enrolment is configured for the management subnets.

### AP controller discovery
The AP's will discover the controller using IPv4 L2 discovery (ADP - Aruba Discovery Protocol). 
AP's will terminate their tunnels on the primary LMS-IP in the AP mgmt VLAN (as defined in the AP-system profile). The AP's will also build a backup tunnel to the secondary controller, this is configured in the HA-profile.

### Validuser ACL (aka IP source-guard)
The validuser ACL controls which entries are programmed in the "user-table" of the Aruba controller. These entries list IP to MAC bindings for all Wireless users.
A user should have one of more entries in this table for IP traffic to be allowed. The source address of the user will be checked against this ACL.

<pre>netdestination6 allowed-v6-source
  network 2001:470:d1e7::/48
  network 2a05:2d01:2017::/48
!
netdestination allowed-v4-source
  network 151.216.0.0 255.255.0.0
  network 145.116.208.0 255.255.240.0
!
ip access-list session validuser
  ipv6 host fe80:: any any  deny 
  ipv6 network fc00::/7 any any  permit 
  ipv6 network fe80::/64 any any  permit 
  ipv6  alias allowed-v6-source any any  permit 
  alias allowed-v4-source any any  permit 
!</pre>

### Broadcast mitigation
We are applying broadcast filtering on the controller in order to drop all "unwanted" broadcast traffic. The Aruba controller in tunnel-mode will do the following:

* Drop all broadcast/multicast except for ARP+ICMPv6
* Convert ARP+ICMPv6 to unicast (this is good for higher data-rates to the clients and also for multiple VLAN's on the same SSID)
* Apply ARP-proxy transparently (clients will not receive ARP-requests for addresses they don't own)
* Apply ICMPv6 ND-proxy transparently (clients will not receive ICMPv6 ND-requests for addresses they don't own)

We'll try to support multicast on all end-user VLAN's. The Aruba controller will perform IGMP-snooping and will allow multicast-streams even-though broadcast-filtering is enabled.
Dynamic multicast optimization (DMO) is applied for better 802.11 rate-control and/or conversion of multicast frames to unicast.

### Find an AP on Juniper EX & HP ProCurve
The Aruba AP's support LLDP. Use this command to find Aruba AP's via LLDP:

<pre>EX:

show lldp neighbors | match AP

ProCurve:

show lldp info remote-device</pre>


## SSID's
This year we'll go with a similar setup like last year. This means:

* More 5GHz
* More crypto (WPA2-Enterprise)
* Less SSID's per band (http://www.revolutionwifi.net/p/ssid-overhead-calculator.html)

In order to have less SSID's per band we are planning to move low-user-count SSID's (like fixip and voc) to the crypto SSID and use dynamic VLAN assignment to put these users in the correct VLAN.

Users can use a random username and password to connect to the crypto SSID; there is no need to distribute any credentials; the RADIUS server will accept anything (only when using EAP-TTLS with PAP).

We will still give the user the choice to connect either to the encrypted or unencrypted SSID as some clients will have issues with WPA2-Enterprise 802.1X.

The usage of band-steering will be avoided as this usually gives issues with MacOS/Linux clients.

| SSID | Band | Crypto | VLAN | Comments |
| ---- | ---- | ------ | ---- | -------- |
| SHA2017 | 5GHz | WPA2-Enterprise | DYNAMIC | Main encrypted SSID, dynamic VLAN assignment, 5GHz only |
| SHA2017-legacy | 2.4GHz | WPA2-Enterprise | DYNAMIC | Main encrypted SSID, dynamic VLAN assignment, 2.4GHz only |
| SHA2017-insecure | 2.4GHz+5GHz | Open | 1192 / DYNAMIC | Main unencrypted SSID, band-steering. Aruba-User-Role: sha2017-public |
| spacenet | 2.4GHz+5GHz | WPA2-Enterprise | 1192 / DYNAMIC | Inter-hackerspace authentication via RADIUS proxy, band-steering. Aruba-User-Role: sha2017-spacenet |
| eduroam | 2.4GHz+5GHz | WPA2-Enterprise | 1192 | Edu authentication via RADIUS proxy, band-steering. Aruba-User-Role: sha2017-eduroam |

### Dynamic assignment

| Username | Password | VLAN | Aruba-User-Role | Comments |
| -------- | -------- | ---- | --------------- | -------- |
| ANY | ANY | 1192 | sha2017-public-dot1x | Users can use a random username/password, everything is accepted with EAP-TTLS & PAP. |
| camp | camp | 1192 | sha2017-public-dot1x | Alternative to any/any (for Windows users or whoever wants to use this) |
| sha2017 | sha2017 | 1192 | sha2017-public-dot1x | Alternative to any/any (for Windows users or whoever wants to use this) |
| guest | guest | 1192 | sha2017-public-dot1x | Alternative to any/any (for Windows users or whoever wants to use this) |
| *@noc | | 2215 | sha2017-noc | NOC users to be dropped into VLAN 2215 |
| tickets | TODO | 1255 | sha2017-tickets | Ticket-scanners to be dropped into VLAN 3002 |
| sysadmin | (via spacenet) | 3001 | sha2017-sysadmin | SysAdmin users to be dropped into VLAN 3001 |
| voc | TODO | 3004 | sha2017-voc | VOC users to be dropped into VLAN 3004 |
| orga | (via spacenet) | 2219 | sha2017-orga | Orga users to be dropped into VLAN 2219 |
| logistics | MAC-AUTH | 2219 | sha2017-logistics | Logistics Windows CE devices to be dropped in VLAN 2219 + inbound firewall on Aruba controller |

FreeRADIUS will return only the Aruba-User-Role attribute, the mapping of user-role to VLAN is done within the Aruba-config.

Username's and passwords are case-sensitive. 

NOC users should connect using the @noc realm, for example: ak47@noc. 

### MAC based assignment
We use this config for that:

<pre>aaa derivation-rules user sha2017 
    set role condition macaddr equals xxx set-value sha2017-logistics 
    set role condition macaddr equals xxx set-value sha2017-logistics 
!</pre>

### Check the certificate

* CN = radius.sha2017.org
* CA = DST Root CA X3
* SHA256 Fingerprint = 20:CE:02:90:2E:2A:79:8E:B5:40:8D:BD:0A:E4:18:A1:AD:5A:C0:BD:6A:09:02:17:A8:F4:46:99:79:A0:B9:C8


## Other config guidelines
* ArubaOS 8.1.x (or newer)
* Only OFDM data-rates, disable data-rates: 2.4GHz: minimal = 18Mbit/s, 5GHz: minimal = 12Mbit/s
* 20MHz channels for 2.4GHz and 5GHz
* Channels 1/5/9/13 for 2.4GHz
* Disable background-scanning for SSH
* Use static channel assignment as much as possible, ARM assignment for the rest
* Beacon-rate: 200ms (on 2.4GHz)
* Airgroup disabled
* Any DPI/stateful SIP/etc firewall-features disabled

# Interface config for APs
APs will be plugged into the end-user VLAN