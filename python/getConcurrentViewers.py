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

    cursor.execute("SELECT videoId FROM video WHERE isAlive = 1;")
    modelList = cursor.fetchall()
    videoIdList = []
    for model in modelList:
        videoIdList.append(model[0])

    length = len(videoIdList)
    s = 50 # 分割数

    n = 0
    for i in videoIdList:
        getConcurrentViewers(videoIdList[n:n+s:1])
        n += s
        if n >= length:
            break

    cursor.close()

JST = datetime.timedelta(hours=9)
def timetrans(strtime):
    stime = datetime.datetime.fromisoformat(strtime[:-1]) + JST
    return stime.replace(microsecond=0)

def getConcurrentViewers(videoIdList):
    os.environ["OAUTHLIB_INSECURE_TRANSPORT"] = "1"

    request = youtube.videos().list(
        part="snippet,liveStreamingDetails",
        id=videoIdList
    )
    response = request.execute()

    # connect
    cursor=db.cursor()
    cursor.execute("USE vtuber_database")
    db.commit()

    # insert string
    update_video = "UPDATE video SET starttime = %s, videoName =  %s WHERE videoId = %s;"
    insert_concurrentViewers = "INSERT INTO concurrentViewers VALUES (null, (SELECT id FROM video WHERE videoId = %s), %s, %s);"

    now = datetime.datetime.now()
    for item in response["items"]:
        if ('liveStreamingDetails' not in item):
            continue
        if ('concurrentViewers' not in item["liveStreamingDetails"]):
            continue

        cursor.execute(update_video, (timetrans(item["liveStreamingDetails"]["actualStartTime"]), item["snippet"]["title"], item["id"]))
        cursor.execute(insert_concurrentViewers, (item["id"], item["liveStreamingDetails"]["concurrentViewers"], now.strftime("%Y-%m-%d %H:%M:%S")))

    db.commit()
    cursor.close()

if __name__ == "__main__":
    main()