import configparser
import os
import googleapiclient.discovery
import datetime
import time
import mysql.connector

ini = configparser.ConfigParser()
ini.read('./config.ini', 'UTF-8')
db=mysql.connector.connect(host=ini['db_info']['host'], user=ini['db_info']['user'], password=ini['db_info']['password'], port=ini['db_info']['port'])

API_KEY = ini['youtube_api']['apiKey']
YOUTUBE_API_SERVICE_NAME = 'youtube'
YOUTUBE_API_VERSION = 'v3'
youtube = googleapiclient.discovery.build(
  YOUTUBE_API_SERVICE_NAME, 
  YOUTUBE_API_VERSION,
  developerKey=API_KEY
  )

def main():
    # connect
    cursor=db.cursor()
    cursor.execute("USE vtuber_database")
    db.commit()

    now = datetime.datetime.now()
    sql = 'SELECT c.channelId FROM channel AS c LEFT JOIN (SELECT * FROM channelData WHERE DATE(createdAt) = "' + now.strftime("%Y-%m-%d") + '") AS tmp ON tmp.channelId = c.id WHERE tmp.subscribers is Null'
    cursor.execute(sql)
    modelList = cursor.fetchall()
    channelIdList = []
    for model in modelList:
        channelIdList.append(model[0])

    length = len(channelIdList)
    s = 50 # 分割数

    n = 0
    for i in channelIdList:
        getSubscribers(channelIdList[n:n+s:1])
        n += s
        if n >= length:
            break

    cursor.close()


def getSubscribers(channelIdList):
    os.environ["OAUTHLIB_INSECURE_TRANSPORT"] = "1"

    request = youtube.channels().list(
        part="snippet,statistics",
        id=channelIdList
    )
    response = request.execute()

    # connect
    cursor=db.cursor()
    cursor.execute("USE vtuber_database")
    db.commit()

    now = datetime.datetime.now()
    for item in response["items"]:
        if ('snippet' not in item):
            continue
        if ('statistics' not in item):
            continue
        if ('subscriberCount' not in item['statistics']):
            continue
        if ('viewCount' not in item['statistics']):
            continue

        channelId = item["id"]
        thumbnail = item["snippet"]['thumbnails']['default']["url"]
        channelName = item["snippet"]["title"]
        cursor.execute("UPDATE channel SET channelName = %s, thumbnail = %s WHERE channelId = %s", (channelName, thumbnail, channelId))
        cursor.execute("INSERT INTO channelData VALUES (null, (SELECT id FROM channel WHERE channelId = %s), %s, %s, %s);", (channelId, item["statistics"]["subscriberCount"], item["statistics"]["viewCount"], now.strftime("%Y-%m-%d %H:%M:%S")))

    db.commit()
    cursor.close()

if __name__ == "__main__":
    main()