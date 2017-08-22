#!/usr/bin/python

# Hacks by eightdot...

import csv
import json
import operator
import pprint


fibers = {}
with open('../fibres.md','r') as fibfile:
    fibreader = csv.reader(fibfile, delimiter='|')
    skip = str(next(fibreader)) +"\n"+ str(next(fibreader)) +"\n"+ str(next(fibreader)) +"\n"+ str(next(fibreader))
    for row in fibreader:
        fibers[row[1].strip()]= {'length':int(row[2].strip()),'cores':int(row[4].strip())}
links=json.loads(open('../design/physical-links.json').read())
lbc = {}    #link by cores
for link in links:
    if (link['medium'] != 'smf'):
        continue
    if not int(link['cores']) in lbc:
        lbc[int(link['cores'])] = []
    lbc[int(link['cores'])].append(link)
slbc = sorted(lbc.items(),key=operator.itemgetter(0),reverse=True)
for cores in slbc:
    slbl = sorted(cores[1],key=lambda x: int(x['length_margin']),reverse=True)
    for lnk in slbl:
        #print ("   " + str(lnk['length_margin']))
        #print(fibers.items())
        ufs = list(filter(lambda f:(int(f[1]['cores']) >= int(lnk['cores'])) and (int(f[1]['length']) >= int(lnk['length_margin'])),fibers.items()))
        #print (ufs[0])
        sufs = sorted(ufs,key=lambda f: int(f[1]['length']))
        if(not len(sufs)):
            print("no fiber for: " + lnk['id'] + ": " + lnk['from'] + " -> " + lnk['to'] + ' !!!!!!!!!')
        else:
            print ("| " + lnk['id'] + " | " + lnk['from'] + " | " + lnk['to'] + " | " + str(lnk['length_margin']) + " | " + str(cores[0]) + " | " + str(sufs[0][0]) + " | " + str(sufs[0][1]['cores']) + " | " + str(sufs[0][1]['length']) + " | ")
            del fibers[sufs[0][0]]
print ("\n# Spare fibres:\n")
for fib in fibers:
    print ("* " + fib)
