#!/usr/bin/env python3
#
# mangala SHA2017
#

import os
import sys
import re
import argparse
import datetime

import json
from pprint import pprint

searchre = None
switchfile = 'design/assigned-switches.json'
verbose = False

#==================================================
# main
#==================================================
def main():
    try:
        with open(switchfile) as j:
            data = json.load(j)
    except Exception as e:
        print('Could not open %s for reading!' % switchfile)
        sys.exit(1)

    if verbose:
        pprint(data)

    for sw in data.keys():
        if re.match(searchre, data[sw]['model']):
            print(sw)


#==================================================
# parse the CLI arguments
#==================================================
def parse_args(cliargs):
    global switchfile
    global searchre
    global verbose

    parser = argparse.ArgumentParser(
            description="""
(c) 2017-%(timestamp)d SHA2017
""" % dict({'timestamp' : datetime.datetime.now().year}),
            epilog='',
            formatter_class=argparse.RawDescriptionHelpFormatter
            )
    parser.add_argument('-f', '--switchfile', help='switch file')
    parser.add_argument('-s', '--searchre', help='search regex', required=True)
    parser.add_argument('-v', '--verbose', help='verbose output', action='count')
    args = parser.parse_args(cliargs)

    if args.switchfile:
        switchfile = args.switchfile
    if args.searchre:
        searchre = args.searchre
    verbose = 0 if args.verbose is None else args.verbose

#==================================================
# main
#==================================================
if __name__ == '__main__':
    parse_args(sys.argv[1:])
    main()

    sys.exit(0)

# EOF
