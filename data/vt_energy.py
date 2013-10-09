import os
import xlrd
import pandas as pd
from scipy import stats

import simplejson as json

#url = 'http://www.efficiencyvermont.com/docs/about_efficiency_vermont/'\
#    + 'initiatives/2006-2011_Usage_and_Savings.xlsx'


DIR = os.path.dirname(os.path.abspath(__file__))

INPUT_DIR = os.path.join(DIR, 'files_in')
OUTPUT_DIR = os.path.join(os.path.join(os.path.dirname(DIR), 'public_html'),
                          'data')

filename = os.path.join(INPUT_DIR, '2006-2011_Usage_and_Savings.xlsx')


def xls_to_dfs(filename):
    num_sheets = xlrd.open_workbook(filename).nsheets

    years = {}
    for sheet_num in xrange(num_sheets):
        #year = str(2006 + sheet_num)
        year = 2006 + sheet_num
        df = pd.read_excel(filename, sheet_num, skiprows=[0,],
                           parse_cols=range(0,8), index_col=0)
        df.columns = pd.Index(['Commercial Usage', 'Residential Usage',
                               'Commercial Savings', 'Residential Savings',
                               'Num Households', 'Avg Res Usage', 'Avg Res Savings'])
        years[year] = df
    return years

def reindex_intersection(dfs):
    y10 = dfs[2010]
    y11 = dfs[2011]

    new_idx = y10.index & y11.index

    ret = dict([ (k, dfs[k].reindex(new_idx)) \
                 for k in dfs.keys() ])
    return ret

def parse_vt_energy(filename):
    years = xls_to_dfs(filename)
    common_towns = reindex_intersection(years)
    return pd.Panel(common_towns)

if __name__ == '__main__':
    panel = parse_vt_energy(filename)
    # >>-------->> original panel's axis-indexes:0                1           2
    years_panel = panel                  # items:years -> df(rows:towns, cols:stats)

                                         #       2                1           0
    stats_panel = panel.transpose(2,1,0) # items:stats -> df(rows:towns, cols:years)

                                         #       1                0           2
    towns_panel = panel.transpose(1,0,2) # items:towns -> df(rows:years, cols:stats)

    towns = {}
    for town in towns_panel.items:
        #df = towns_panel[x].reset_index().rename(columns={'index': 'Year'})
        df = towns_panel[town]
        d = json.loads(df.to_json())

        ret = []
        for k in d.keys():
            ret.append( (k, map(lambda t: {'x': int(t[0]), 'y': t[1]}, d[k].items())) )
        towns[town.title()] = dict(ret)

    with open(os.path.join(OUTPUT_DIR, 'vt_energy_towns.json'), 'w') as f:
        f.write(json.dumps(towns))

