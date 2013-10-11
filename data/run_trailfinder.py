import os
import requests
import simplejson as json
import xml.etree.cElementTree as ET
from bs4 import BeautifulSoup as Soup
from urllib import urlencode


DIR = os.path.abspath(os.path.dirname(__file__))
DATA_DIR = os.path.join(DIR, 'trailfinder')
TRAILS_XML = os.path.join(DATA_DIR, 'trailfinder.xml')

def eltext(el):
    return el.text.strip() if el.text not in [None, '', 'null', 'NULL'] else None

def node_to_dict(node):
    return dict([(el.tag, eltext(el)) for el in node])

def rename_key(d, old, new):
    d[new] = d.pop(old)
    return d

def trail_node_parser(node):
    def useicons(el):
        imgs = Soup(el.text).findAll('img')
        def img_d(img):
            return {'title': img['title'],
                    'src': img['src']}
        return [img_d(img) for img in imgs]

    def html_text(x):
        html = eltext(x)
        if not html:
            return None
        html = html.replace('\\n', '\n').replace('\\r', '').replace("\\'", "'")
        return '\n'.join(l.strip() for l in Soup(html).get_text().splitlines())

    mappings = {'id': lambda x: int(eltext(x)) if eltext(x) else None,
                'name': lambda x: eltext(x).replace("\\'", "'") if eltext(x) else None,
                'subname': eltext,
                'trailhead': html_text,
                'length': lambda x: float(eltext(x)) if eltext(x) else None,
                'lengthunits': eltext,
                'surface': html_text,
                'features': None,
                'description': eltext,
                'useicons': useicons,
                'TrailMarker': node_to_dict,
                'TrailLine': None,
                }
    ret = dict([
        (el.tag, mappings[el.tag](el))\
        for el in node if mappings[el.tag] is not None])
    rename_key(ret, 'useicons', 'trailuses')
    rename_key(ret, 'TrailMarker', 'trailmarker')
    rename_key(ret, 'surface', 'features')
    return (ret['name'], ret)

def trailuses_lookup(parsed):
    imgs = [x['trailuses'] for x in parsed.values()]
    icons = map(lambda l: set([(img['title'], img['src']) for img in l]), imgs)
    ret = icons[0]
    for i in xrange(len(icons)):
        ret = ret.union(icons[i])
    d = dict(ret)
    d.pop('')
    return d

def parse_trails():
    with open(TRAILS_XML, 'r') as f:
        tree = ET.ElementTree(file=f)
    trails = (t.getchildren() for t in tree.iter('Trail'))
    parsed = dict(map(trail_node_parser, trails))
    uses_lookup = trailuses_lookup(parsed)
    for key, value in parsed.items():
        parsed[key]['trailuses'] = [x['title'] for x in value['trailuses']]
        parsed[key]['address'] = get_address(value['trailmarker'])
    return {'trailuses_lookup': uses_lookup,
            'trails': parsed}

def get_address(d):
    url = 'http://nominatim.openstreetmap.org/search/?'\
        + urlencode({'q': '%s, %s' % (d['lat'], d['lng']),
                     'format': 'json',
                     'addressdetails': 1})
    results = json.loads(requests.get(url).text)
    ret = results[0]['address'] if results else {}
    if 'hamlet' in ret:
        ret['city'] = ret.pop('hamlet')
    print d['id']
    return ret

def run():
    parsed = parse_trails()
    with open(os.path.join(DATA_DIR, 'trailfinder.json'), 'w') as f:
        f.write(json.dumps(parsed, indent=2))

if __name__ == '__main__':
    run()
    