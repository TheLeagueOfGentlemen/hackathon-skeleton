# -*- coding: utf-8 -*-

import os
import time
import simplejson as json
import requests
import grequests

from lxml import html as LH


PLACES_URL = 'http://www.diginvt.com/search-results/?Categories__ID=&RegionID='
EVENTS_URL = 'http://www.diginvt.com/events/'
TRAILS_URL = 'http://www.diginvt.com/trails/'

DIR = os.path.abspath(os.path.dirname(__file__))
DATA_DIR = os.path.join(DIR, 'diginvt')

PLACES_DIR = os.path.join(DATA_DIR, 'places')
EVENTS_DIR = os.path.join(DATA_DIR, 'events')
TRAILS_DIR = os.path.join(DATA_DIR, 'trails')


def batch(list_, size, sleep=None):
    len_ = len(list_)
    for i in xrange((len_ / size) + 1):
        start_idx = i * size
        end_idx = (i + 1) * size
        if end_idx > len_:
            end_idx = len_

        yield list_[start_idx:end_idx]
        if sleep: time.sleep(sleep)

def dl_and_save(urls, save_dir):
    def get_fname(url):
        return url[url.rfind('/')+1:] + '.html'

    errors = []
    for urls_ in batch(urls, 10, 5):
        reqs = (grequests.get(url) for url in urls_)
        for r in grequests.map(reqs):
            if r.status_code == 200:
                with open(os.path.join(save_dir, get_fname(r.url)), 'w') as f:
                    f.write(r.text.encode('utf-8'))
            else:
                errors.append((r.status_code, r.url))
    return errors

def __load_and_parse(fdir, parser_fn, page):
    def fname_to_url(fname, page):
        return 'http://www.diginvt.com/%s/detail/%s' % (page,
                                                        fname[:fname.rfind('.')])
    ret = {}
    for fname in os.listdir(fdir):
        if not os.path.isfile(os.path.join(fdir, fname)):
            continue
        with open(os.path.join(fdir, fname), 'r') as f:
            html = f.read()
        ret[fname_to_url(fname, page)] = parser_fn(html)
    return ret


def cache_places(save_dir):
    html = requests.get(PLACES_URL).text
    urls = extract_places_links(html)

    errors = dl_and_save(urls, save_dir)
    return errors

def cache_events(save_dir):
    html = requests.get(EVENTS_URL).text
    urls = extract_events_links(html)
    errors = dl_and_save(urls, save_dir)
    return errors

# ---------------------------------------------------------------- #
#  Parsing
# ---------------------------------------------------------------- #

def extract_places_links(html):
    start_str = '<a class=\\"button learnmore\\"'
    start_idx = 0
    ret = []
    while True:
        idx = html.find(start_str, start_idx)
        if idx == -1:
            break
        end_idx = html.find('">', idx)
        a = html[idx:end_idx].replace('\\', '')
        href = a[a.find('href="')+len('href="'):]
        ret.append('http://www.diginvt.com' + href)
        start_idx = end_idx

    return ret

def extract_events_links(html):
    lh = LH.document_fromstring(html)

    elist = lh.get_element_by_id('event-list')
    divs = [li.find('div') for li in elist.find('ul').findall('li') if li.find('div')]
    hrefs = [div.find('h3').find('a').get('href') for div in divs]
    return ['http://www.diginvt.com' + href for href in hrefs]


def get_cls(lh, cls):
    elems = lh.find_class(cls)
    return unicode(elems[0].text_content().strip()) if elems else None

def www_info(lh):
    ret = {'website': None, 'email': None}
    www_info = lh.find_class('online')
    if not www_info:
        return ret

    for div in www_info[0].getchildren():
        href = div.find('a').get('href')
        if 'mailto:' in href:
            ret['email'] = href.replace('mailto:', '')
        else:
            ret['website'] = href
    return ret

def get_address(lh):
    address = lh.find_class('street-address')
    return u'\n'.join([unicode(x.text_content().strip()) for x in address])

def categories_and_seasons(lh):
    ret = {'categories': [], 'seasons': []}
    otherinfo = lh.find_class('otherinfo')
    if not otherinfo:
        return ret

    for detail in otherinfo[0].getchildren():
        name = detail.find('h4')
        if name is None:
            continue
        name = name.text.lower()
        if name == 'category(s)':
            categories = [a.text_content().split(',') for a in detail.findall('p')][0]
            ret['categories'] = [x.strip() for x in categories]
        elif name == 'best visited in':
            ret['seasons'] = [x.strip() for x in detail.find('p').text.split(',')]
    return ret

def parse_place(html):
    lh = LH.document_fromstring(html)

    def get_hours(lh):
        p = lh.find_class('address')[0].getchildren()[-1]
        if p.tag == 'p':
            return unicode(p.text.strip())
        return None

    d = {'name': get_cls(lh, 'fn org'),
         'description': get_cls(lh, 'description'),
         'hours': get_hours(lh),
         'address': get_address(lh),
         'town': get_cls(lh, 'locality'),
         'state': get_cls(lh, 'region'),
         'zipcode': get_cls(lh, 'postal-code'),
         'phone': get_cls(lh, 'tel'),
         }
    d.update(www_info(lh))
    d.update(categories_and_seasons(lh))
    return d

def parse_places():
    return __load_and_parse(PLACES_DIR, parse_place, 'places')

def parse_event(html):
    lh = LH.document_fromstring(html)

    d = {'name': get_cls(lh, 'fn org'),
         'description': get_cls(lh, 'description'),
         'date': get_cls(lh, 'date').split(u'\u2014')[0].strip(),
         'times': [t.strip() for t in get_cls(lh, 'times').split(u'\u2014')]\
                   if get_cls(lh, 'times') else None,
         'address': get_address(lh),
         'town': get_cls(lh, 'locality'),
         'state': get_cls(lh, 'region'),
         'zipcode': get_cls(lh, 'postal-code'),
         'phone': get_cls(lh, 'tel'),
         'categories': categories_and_seasons(lh)['categories'],
         }
    d.update(www_info(lh))
    return d

def parse_events():
    return __load_and_parse(EVENTS_DIR, parse_event, 'events')

def parse_trail(html):
    lh = LH.document_fromstring(html)

    def get_name():
        div = lh.find_class('block pageheader clr')[0]
        return div.find('h1').text

    def get_trail_places():
        ul = lh.find_class('trails clr')[0]
        hrefs = [li.find('div').find('h3').find('a').get('href')\
                 for li in ul.getchildren()]
        return {'places': ['http://www.diginvt.com' + x for x in hrefs]}

    d = {'name': get_name(),
         'description': get_cls(lh, 'trail-description clr'),
         }
    d.update(categories_and_seasons(lh))
    d.update(get_trail_places())
    return d


def get_trails_urls():
    def get_trails_indexes():
        trails_urls = ['http://www.diginvt.com/trails/TrailSearchForm?Keyword='\
                    + '&RegionID=&TownID=&action_doSearch=Search&start=%i' % i\
                    for i in xrange(0,22,3)]
        reqs = (grequests.get(url) for url in trails_urls)
        return [r.text for r in grequests.map(reqs)]

    def get_trails_page_urls(trails_indexes):
        ret = []
        for html in trails_indexes:
            lh = LH.document_fromstring(html)
            trails = lh.find_class('trails clr')[0].getchildren()

            for trail in trails:
                url = trail.find('h4').find('a').get('href')
                ret.append(url)
        return ret

    return get_trails_page_urls(get_trails_indexes())

def cache_trails():
    errors = dl_and_save(get_trails_urls(), TRAILS_DIR)
    return errors

def parse_trails():
    return __load_and_parse(TRAILS_DIR, parse_trail, 'trails')

if __name__ == '__main__':
    #cache_places(PLACES_DIR)
    #cache_events(EVENTS_DIR)
    #cache_trails(TRAILS_DIR)

    d = {'places': parse_places(),
         'events': parse_events(),
         'trails': parse_trails()}

    with open(os.path.join(DATA_DIR, 'parsed_data.json'), 'w') as f:
        f.write(json.dumps(d, indent=2))
