import os
import requests
from bs4 import BeautifulSoup as Soup
from lxml import html as LH
import simplejson as json
from pprint import pprint

DATA_DIR = os.path.join(os.path.dirname(__file__), 'ski_vt')

def dl(url):
    return requests.get(url).text

def get_resort_links(html):
    lh = LH.document_fromstring(html)

    kids = lh.get_element_by_id('resort-sorter-list').getchildren()
    alpine = []
    nordic = []
    in_nordic = False
    for kid in kids[1:]:
        if kid.tag == 'h2':
            in_nordic = True
        if kid.tag == 'div':
            if not in_nordic:
                alpine.append(kid)
            else:
                nordic.append(kid)
    def get_href(node):
        return 'http://www.skivermont.com'\
               + node.find_class('resort-list-details')[0].find('a').get('href')

    a = [get_href(a) for a in alpine]
    n = [get_href(n) for n in nordic]
    b = set(a).intersection(set(n))

    return {'alpine': list(set(a).difference(b)),
            'nordic': list(set(n).difference(b)),
            'both': list(b)}

def parse_contact(lh):
    ret = {}
    try:
        div = lh.get_element_by_id('contact')
    except:
        return ret

    ret['name'] = div.get_element_by_id('resort-logo').find('img').get('alt')
    address = [x.strip() for x in div.find_class('intro address')[0].itertext() if x.strip()]
    addy = address[address.index('Resort Address')+1:address.index('Email:')]
    town, st, zip_ = addy[-1].partition(', VT ')
    ret['address'] = {'street': ', '.join(addy[:-1]),
                      'town': town,
                      'state': 'VT',
                      'zipcode': zip_,
                      }
    ret['email'] = address[address.index('Email:')+1]
    ret['website'] = div.find_class('resort-website')[0].get('href')
    return ret

def parse_stats(lh, type_):
    def parse_section(node):
        return node.find('h2').text_content().strip()

    def parse_stats(node):
        def l_to_d(l):
            ret = {}
            for i in xrange(0, len(l), 2):
                ret[l[i]] = l[i+1]
            return ret

        def lifts(l):
            return {l[0]: l[1:]}

        nodes = node.findall('p')
        ret = {}
        for node in nodes:
            stats = [l.strip().replace(':', '') for l in node.itertext() if l.strip()]
            if stats[0] == 'Total Lifts':
                ret.update(lifts(stats))
            else:
                ret.update(l_to_d(stats))
        return ret

    sections = type_ #lh.find_class('heading-wrapper')
    stats = lh.find_class('mountain stats')
    sections = zip(*[sections, stats])

    ret = []
    for section, stats in sections:
        ret.append((section, parse_stats(stats)))
    return dict(ret)

def parse_ski_resort(html, type_):
    lh = LH.document_fromstring(html)
    ret = parse_contact(lh)
    if not ret:
        return ret
    ret['stats'] = parse_stats(lh, type_)
    ret['type'] = type_
    ret['description'] = '\n'.join(
        [x.text.strip() for x in lh.find_class('description')[0].findall('p')])
    return ret

def parse_ski_resorts(url_index):
    ret = []
    types = {'alpine': ['Alpine'],
             'nordic': ['Nordic'],
             'both': ['Alpine', 'Nordic']}
    
    def fname_url(fname):
        return 'http://www.skivermont.com/resorts/resort-info/' + fname[:fname.rfind('.')]

    for fname in os.listdir(DATA_DIR):
        fpath = os.path.join(DATA_DIR, fname)
        if os.path.isfile(fpath) and fpath.endswith('.html'):
            with open(fpath, 'r') as f:
                html = f.read()
            url = fname_url(fname)
            for k, urls in url_index.items():
                if url in urls:
                    type_ = types[k]
            resort = parse_ski_resort(html, type_)
            if 'name' in resort:
                ret.append((resort['name'], resort))
    return dict(ret)


if __name__ == '__main__':
    urls = get_resort_links(requests.get('http://www.skivermont.com/resorts').text)
    
    resorts = parse_ski_resorts(urls)
    with open(os.path.join(DATA_DIR, 'ski_resorts.json'), 'w') as f:
        f.write(json.dumps(resorts, indent=2))

