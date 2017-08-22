# Allocations
We have requested temporary IP space and will receive a /16 IPv4 prefix on July 15th.

We will use SURFnet/EventInfra /20 IPv4 space for our internal core and distribution networks and for some prioritised access networks.

* ASN: 1150
* IPv4 PI permanent: 145.116.208.0/20
* IPv4 PI temporary: 151.216.0.0/16 (temporary PI)
* IPv6 PI: 2a05:2d01:2017::/48 (from 2a05:2d01::/32 PI)
* MNT: EVENTINFRA-MNT, CHAOS-MNT
* VLAN ranges:
  * 8xx: NOC VLANs segmented smaller than /24
  * 9xx: transfer nets
  * 1xxx: 151.216.xxx./24
  * 2xxx: 145.116.xxx./24
  * 30xx: L2 only stuff
  * 31xx: VoIP/ArtNet/L2-switch mgmt (locally routed)
  * 40xx: L2 only ISP transfer

# IPAM
We have an IPAM available. (not linked on GitHub)

# Local routed subnets
ArtNet, L2 switch management & VoIP need some subnets which are locally routed, we are planning to use RFC6598 space for this. These subnets might also need ACLs so visitors cannot reach devices in these subnets.

To make templating easier VLAN-IDs are re-used:

* 3100: L2 switch management
* 3101: ArtNet
* 3102: VoIP

To assign a subnet these rules are applied using the base end-user VLAN (of the router):

* L2 switch management:
 * if $VLAN > 2000: 100.65.($VLAN-2000).0/24
 * elsif $VLAN > 1000: 100.64.($VLAN-1000).0/24
 * Always assign subnet when generating router config 
* ArtNet (always assign subnet):
 * if $VLAN > 2000: 100.67.($VLAN-2000).0/24
 * elsif $VLAN > 1000: 100.66.($VLAN-1000).0/24
 * Always assign subnet when generating router config
* VoIP
 * if $VLAN > 2000: 100.69.($VLAN-2000).0/24
 * elsif $VLAN > 1000: 100.68.($VLAN-1000).0/24
 * Only assign a subnet when present in IPAM

For IP assignment:

* L2 switch management: lookup entry in IPAM to determine IPv4 management address
* ArtNet: DHCP assignment via ArtNet DHCP server
* VoIP: DHCP assignment via VoIP DHCP server
