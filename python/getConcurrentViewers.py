import configparser
import os
import googleapiclient.discovery
import datetime
import time
import mysql.connector

ini = configparser.RawConfigParser()
ini.read('./config.ini', 'UTF-8')
db=mysql.connector.connect(
    host=ini.get('db_info','host'),
    user=ini.get('db_info','user'),
    password=ini.get('db_info','password'),
    port=ini.get('db_info','port'),
    database=ini.get('db_info','db')
)

API_KEY = ini.get('youtube_api','apiKey')
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

    cursor.execute("SELECT videoId FROM video WHERE isAlive = 1 AND ((scheduledStartTime IS NOT NULL AND scheduledStartTime > NOW() - INTERVAL 1 DAY AND scheduledStartTime < NOW() + INTERVAL 5 MINUTE) OR (scheduledStartTime IS NOT NULL AND scheduledStartTime < updatedAt + INTERVAL 1 DAY AND starttime IS NOT NULL) OR scheduledStartTime IS NULL);")
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

    videoDataList = {}
    concurrentViewerList =[]
    now = datetime.datetime.now()
    for item in response["items"]:
        id = item["id"]
        if ('liveStreamingDetails' not in item):
            # liveStreamingDetailsがない場合は、ただの動画
            videoDataList[id] = (None, None, None, 2, item["snippet"]["title"], id)
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
        videoDataList[id] = (scheduledStartTime, actualStartTime, actualEndTime, isAlive, item["snippet"]["title"], id)

        if ('concurrentViewers' not in item["liveStreamingDetails"]):
            continue
        concurrentViewerList.append((item["id"], item["liveStreamingDetails"]["concurrentViewers"]))

    delList = []
    for videoId in videoIdList:
        if (videoId not in videoDataList):
            # liveStreamingDetailsがない場合は、削除対象
            cursor.execute("UPDATE video SET isAlive = 3, updatedAt = now() WHERE videoId = '" +  videoId + "';", ( ))

    tmp = list(videoDataList.values())
    cursor.executemany("UPDATE video SET scheduledStartTime = %s, starttime = %s,  endtime = %s, isAlive = %s, videoName =  %s, updatedAt = now() WHERE videoId = %s;", tmp)
    cursor.executemany("INSERT INTO concurrentViewers (id, videoId, viewers, createdAt) VALUES (null, (SELECT id FROM video WHERE videoId = %s), %s, now());", concurrentViewerList)
    db.commit()
    cursor.close()

if __name__ == "__main__":
    main()
