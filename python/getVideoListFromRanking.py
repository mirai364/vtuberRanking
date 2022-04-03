import configparser
import os
import feedparser
import mysql.connector
import numpy as np
from bs4 import BeautifulSoup
import requests
import datetime


ini = configparser.RawConfigParser()
ini.read('./config.ini', 'UTF-8')

cnx=mysql.connector.connect(
    host=ini.get('db_info','host'),
    user=ini.get('db_info','user'),
    password=ini.get('db_info','password'),
    port=ini.get('db_info','port'),
    database=ini.get('db_info','db')
)

headers = {
    "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36",
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
}

def main():
    curChild = cnx.cursor()

    session = requests.Session()
    url = "https://live-ranking.userlocal.jp/vtuber-ranking"
    videoMap = {}
    req = session.get(url, headers=headers)
    bsObj = BeautifulSoup(req.text, "html.parser")
    values = bsObj.find_all('div', class_='live-area')
    now = datetime.datetime.now()

    for ranking in values:
        videoName = ranking.find_all('a')[5].get_text().strip()
        videoId = ranking.find_all('a')[5].get('href').split('/')[3].split('v=')[1]
        videoMap.update({videoId: videoName})

    key = list(videoMap.keys())
    curChild.execute("SELECT videoid FROM video WHERE videoId IN ('" + "','".join(key) + "')")
    modelList = curChild.fetchall()
    videoIdList = []
    for model in modelList:
        videoIdList.append(model[0])

    seqs = [];
    for key, value in videoMap.items():
        if key not in videoIdList:
            seqs.append((key, value))

    curChild.executemany("INSERT INTO video VALUES (null, 0, %s, %s, null, null, null, -1, null);", seqs)
    curChild.close()
    cnx.commit()

if __name__ == "__main__":
    main()