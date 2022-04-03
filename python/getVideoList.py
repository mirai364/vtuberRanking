import configparser
import os
import feedparser
import mysql.connector
from multiprocessing import Pool
import googleapiclient.discovery
import datetime
import time

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

    global youtube
    API_KEY = ini.get('youtube_api','apiKey2')
    YOUTUBE_API_SERVICE_NAME = 'youtube'
    YOUTUBE_API_VERSION = 'v3'
    youtube = googleapiclient.discovery.build(
      YOUTUBE_API_SERVICE_NAME,
      YOUTUBE_API_VERSION,
      developerKey=API_KEY
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

    sql = 'SELECT videoId FROM video WHERE isAlive = -1'
    cursor.execute(sql)
    modelList = cursor.fetchall()
    videoIdList = []
    for model in modelList:
        videoIdList.append(model[0])
    videoIdList = [videoIdList[i:i+50] for i in range(0,len(videoIdList),50)]

    p = Pool(processes=12, initializer=init)
    p.map(getVideo, videoIdList)
    p.close()
    p.join()

JST = datetime.timedelta(hours=9)
def timetrans(strtime):
    stime = datetime.datetime.fromisoformat(strtime[:-1]) + JST
    return stime.replace(microsecond=0)

def getVideo(videoIdList):
    os.environ["OAUTHLIB_INSECURE_TRANSPORT"] = "1"
    request = youtube.videos().list(
        part="snippet,liveStreamingDetails",
        id=videoIdList
    )
    response = request.execute()

    dataList = []
    for item in response["items"]:
        if ('liveStreamingDetails' not in item):
            # liveStreamingDetailsがない場合は、ただの動画
            dataList.append((None, None, None, 2, item["id"]))
            continue

        isAlive = 1
        actualEndTime = None
        if ('actualEndTime' in item["liveStreamingDetails"]):
            actualEndTime = timetrans(item["liveStreamingDetails"]['actualEndTime'])
            isAlive = 0
        actualStartTime = None
        if ('actualStartTime' in item["liveStreamingDetails"]):
            actualStartTime = timetrans(item["liveStreamingDetails"]['actualStartTime'])
        scheduledStartTime = None
        if ('scheduledStartTime' in item["liveStreamingDetails"]):
            scheduledStartTime = timetrans(item["liveStreamingDetails"]['scheduledStartTime'])

        dataList.append((item["snippet"]["channelId"], scheduledStartTime, actualStartTime, actualEndTime, isAlive, item["id"]))

    curChild = cnx.cursor()
    curChild.executemany("UPDATE video SET channelId = %s, scheduledStartTime = %s, starttime = %s,  endtime = %s, isAlive = %s, updatedAt = now() WHERE videoId = %s;", dataList)
    curChild.close()
    cnx.commit()

if __name__ == "__main__":
    main()