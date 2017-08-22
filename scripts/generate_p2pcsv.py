#!/usr/bin/env python3
#
# generate_p2pcsv.py - Generate netbox CSVs for P2P links based on logical network map
#

import ipaddress
import json
import sys

vlan_csv = 'netbox_VLANs.csv'
prefix_csv = 'netbox_prefixes.csv'
address_csv = 'netbox_addresses.csv'

if len(sys.argv) < 5:
    sys.stderr.write('Usage: %s <json_file> <v4_prefix> <v6_prefix> <start_vlan>\n' % (sys.argv[0]))
    sys.exit(1)

JSON_IN = sys.argv[1]
PREFIX4_START = sys.argv[2]
PREFIX6_START = sys.argv[3]
VLAN_START = int(sys.argv[4])

with open(JSON_IN) as f:
    content = ''.join(f.readlines())
logical = json.loads(content)
f.close()

prefix_v4 = PREFIX4_START.split('/')[0]
prefix_v6 = PREFIX6_START.split('/')[0] + prefix_v4.split('.')[3]
vlan_id = VLAN_START
fv = open(vlan_csv,'w')
fp = open(prefix_csv,'w')
fa = open(address_csv,'w')

device_types = dict()
for device in logical['devices']:
    device_types[logical['devices'][device]['hostname']] = logical['devices'][device]['type']

fv.write('site,group_name,vid,name,tenant,status,role,description\n')
fp.write('prefix,vrf,tenant,site,vlan_group,vlan_vid,status,role,is_pool,description\n')
fa.write('address,status,description\n')
for link in logical['links']:
    if not link['from'] or not link['to']:
        continue
    if device_types[link['from']] != 'L3' or device_types[link['to']] != 'L3':
        continue
    fv.write('SHA2017,Field,%d,P2P-%s_%s,,Active,,\n' % (vlan_id,link['from'].upper(),link['to'].upper()))
    fp.write('%s/31,,,SHA2017,Field,%d,Active,,,P2P-%s_%s\n' % (prefix_v4,vlan_id,link['from'].upper(),link['to'].upper()))
    fp.write('%s/127,,,SHA2017,Field,%d,Active,,,P2P-%s_%s\n' % (prefix_v6,vlan_id,link['from'].upper(),link['to'].upper()))

    if 'port_a_logical' in link and link['port_a_logical'] != '':
       link['port_a'] = link['port_a_logical']

    fa.write('%s/31,Active,%s_%s\n' % (prefix_v4,link['from'].upper(),link['port_a']))
    fa.write('%s/127,Active,%s_%s\n' % (prefix_v6,link['from'].upper(),link['port_a']))

    addr_v4 = str(ipaddress.IPv4Address(prefix_v4) + 1)
    addr_v6 = PREFIX6_START.split('/')[0] + addr_v4.split('.')[3]

    if 'port_b_logical' in link and link['port_b_logical'] != '':
       link['port_b'] = link['port_b_logical']

    fa.write('%s/31,Active,%s_%s\n' % (addr_v4,link['to'].upper(),link['port_b']))
    fa.write('%s/127,Active,%s_%s\n' % (addr_v6,link['to'].upper(),link['port_b']))

    prefix_v4 = str(ipaddress.IPv4Address(prefix_v4) + 2)
    prefix_v6 = PREFIX6_START.split('/')[0] + prefix_v4.split('.')[3]
    vlan_id = vlan_id + 1

fv.close()
fp.close()

