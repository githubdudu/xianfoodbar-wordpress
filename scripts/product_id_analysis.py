# Python analysis of product id in the xianfoodbar accross 10 stores
# To check if the product id is consistent across stores
# @author: dudu

path = '/mnt/c/Users/dd/Desktop/西安饭庄项目'


file_prefix = path + '/wc-product-export-8-6-2026-'
store_black = ['albany', 'mtalbert', 'hobson']
store_red = ['city', 'dominion', 'hamilton', 'howick', 'northcote', 'panmure', 'rosedale']
file_extension = '.csv'

import csv

products = []
for store in store_red:
    file_name = file_prefix + store + file_extension
    print(file_name)
    with open(file_name, 'r') as csvfile:
        reader = csv.reader(csvfile)
        for row in reader:
            type = row[0]
            name = row[4]
            products.append((type, name))
  
products.sort()

with open(path + '/product_id_analysis.csv', 'w', encoding='utf-8') as csvfile:
    writer = csv.writer(csvfile)
    writer.writerow(['Type', 'Name'])
    writer.writerows(products)