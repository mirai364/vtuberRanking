import configparser
import os
import feedparser
import mysql.connector
import numpy as np
from multiprocessing import Pool

def init():
    ini = configparser.RawConfigParser()
    ini.read('./config.ini', 'UTF-8')

    global cnx
    cnx=mysql.connector.connect(
        host=ini.get('db_info','host'),
        user=ini.get('db_info','user'),
        password=ini.get('db_info','password'),
        port=ini.get('db_info','port'),
        database=ini.get('db_info','db')
    )

def main():
    ini = configparser.RawConfigParser()
    ini.read('./config.ini', 'UTF-8')
    db=mysql.connector.connect(
        host=ini.get('db_info','host'),
        user=ini.get('db_info','user'),
        password=ini.get('db_info','password'),
        port=ini.get('db_info','port'),
        database=ini.get('db_info','db')
    )
    cursor = db.cursor()

    sql = 'SELECT channelId FROM channel'
    cursor.execute(sql)
    modelList = cursor.fetchall()
    tmp = np.array(modelList)
    channelIdList = tmp[:, 0]

    p = Pool(processes=12, initializer=init)
    p.map(getVideo, channelIdList)
    p.close()
    p.join()

def getVideo(channelId):
    curChild = cnx.cursor()

    RSS_URL = 'https://www.youtube.com/feeds/videos.xml?channel_id=' + channelId
    videoMap = {}
    d = feedparser.parse(RSS_URL)
    for entry in d.entries:
        videoMap.update({entry.yt_videoid: entry.title})

    key = list(videoMap.keys())
    curChild.execute("SELECT videoid FROM video WHERE channelId = %s AND videoId IN ('" + "','".join(key) + "')", (channelId, ))
    modelList = curChild.fetchall()
    videoIdList = []
    for model in modelList:
        videoIdList.append(model[0])

    seqs = [];
    for key, value in videoMap.items():
        if key not in videoIdList:
            seqs.append((channelId, key, value))

    curChild.executemany("INSERT INTO video VALUES (null, %s, %s, %s, null, null, null, -1, null);", seqs)
    curChild.close()
    cnx.commit()

if __name__ == "__main__":
    main()