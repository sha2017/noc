# Teams


## Uplink
Tasks:

* Arrange uplink sponsors and physical connections to our border router
* Arrange IP space assignment from RIPE
* Set up external BGP routers
* Set up BGP routing to upstreams on the external routers
* Traffic engineering
* Manage access-lists on external routers for antispoofing, antiabuse
* Manage uplinks from the field


## Core/distribution
Tasks:

* Implement/deploy distribution field switches/routers
* Implement/deploy optical & ethernet & IP transport at the field
* IP planning

## Access staging
Tasks:

* Configure access switches

## NOC/orga datacenter
Tasks:

* Talk with scouting about permanent concrete buildings for datacenter
* Arrange power
* Arrange cooling
* Arrange racks

## Public colo/Gazebo
Tasks:

* Setup public colo facility
* Coordinate with power team
* Coordinate visitor access to colo facility

## Wireless
Tasks:

* Acquire wireless access points and controllers
* Central wireless infra (controllers, RADIUS, etc)
* Planning AP and channel distribution
* Monitoring wireless (AirWave/Graphite/Grafana)
* Physical mounting of APs (coordinate with deployment sub-team)

## Servers & services
Tasks:

* Bring flightcase to the event
* Install OS + full disk encryption
* Make sure all core Services work
* Help others with installing their applications/services, provide them with pre-installed servers and take care of them during the event
* Wipe servers afterwards

### Services
Tasks (not sure if still accurate for SHA):

* Auth DNS
* Recursive DNS
* DHCP
* jumphost
* roundup/ticketing system
* libreNMS (auto-installed via puppet)
* oxidized
* NAT64 machine
* BIRD VM
* Other machines for people who need anything

## Monitoring
Tasks:

* Icinga (for the usual switch-dashboard)
* Prometheus and/or Graphite and/or InfluxDb (whatever works best :-))
* AS-Stats
* Dashboard (Grafana)

## Abuse
Tasks:

* monitor abuse-phone
* monitor abuse@
* handle abuse requests

## Overview
Tasks:

* Keeping an overview of progress
* Interfacing with PL
* Interfacing with orga/other teams
* Making the unpopular decisions

## Cabling & deployment
Tasks:

* Deploy fibres on-site
* Deploy copper on-site, RJ45 termination etc
* Fibre splicing where needed
* Deploy datenklo switches & APs

## Planning/design
Tasks:

* Mapping - plan and map cables on the terrain
* Create physical network diagram
* Create logical network diagram
* Planning of available network equipment

## NOC Helpdesk
Tasks:

* Coordinate NOC helpdesk shifts --> coordinate with Team:Volunteers to staff shifts
* Coordinate with Infodesk
* Handle first line support issues and if needed escalate to next level
* Coordinate cable rounds (plugin visitor cables into Datenklo switches)