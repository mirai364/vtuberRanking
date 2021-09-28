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
    return
    exit
    # connect
    cursor=db.cursor()
    cursor.execute("USE vtuber_database")
    db.commit()

    cursor.execute('SELECT videoId FROM video')
    modelList = cursor.fetchall()
    videoIdList = []
    for model in modelList:
        videoIdList.append(model[0])

    length = len(videoIdList)
    s = 50 # 分割数

    n = 0
    for i in videoIdList:
        getSubscribers(videoIdList[n:n+s:1])
        n += s
        if n >= length:
            break

    cursor.close()


def getSubscribers(videoIdList):
    os.environ["OAUTHLIB_INSECURE_TRANSPORT"] = "1"

    request = youtube.videos().list(
        part="id,snippet",
        id=videoIdList
    )
    response = request.execute()

    # connect
    cursor=db.cursor()
    cursor.execute("USE vtuber_database")
    db.commit()

    for item in response["items"]:
        if ('snippet' not in item):
            continue

        videoId = item["id"]
        videoName = item["snippet"]['title']
        cursor.execute("UPDATE video SET videoName = %s WHERE videoId = %s", (videoName, videoId))

    db.commit()
    cursor.close()

if __name__ == "__main__":
    main()