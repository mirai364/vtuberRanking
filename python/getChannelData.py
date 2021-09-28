import configparser
from bs4 import BeautifulSoup
import requests
import mysql.connector
import datetime

ini = configparser.ConfigParser()
ini.read('./config.ini', 'UTF-8')
db=mysql.connector.connect(host=ini['db_info']['host'], user=ini['db_info']['user'], password=ini['db_info']['password'], port=ini['db_info']['port'])

headers = {
    "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36",
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
}

def main():
    # connect
    cursor=db.cursor()
    cursor.execute("USE vtuber_database")
    db.commit()

    for number in range(1,400):
        session = requests.Session()
        url = "https://vtuber-post.com/ranking/index.php?page="+str(number)
        req = session.get(url, headers=headers)
        bsObj = BeautifulSoup(req.text, "html.parser")
        values = bsObj.find_all('div', class_='clearfix')[2].find_all('div', class_='clearfix')
        values.pop(0)
        now = datetime.datetime.now()
        for ranking in values:
            if len(ranking.find_all('p', class_='thumb')) > 0 :
                # 先にchannelのinsert
                channelId = ranking.find('p', class_='thumb').find('a').get('href').split("/")[2].split("?id=")[1]
                channelName = ranking.find('p', class_='name').find('a').get_text()
                nameList = ranking.find('p', class_='name').find_all('a')
                groupName = None
                if len(nameList) > 1:
                    groupName = nameList[1].get_text()

                cursor.execute("SELECT id FROM channel WHERE channelId = %s", (channelId, ))
                model = cursor.fetchone()
                channelIdInt = 0
                if model == None:
                    cursor.execute("INSERT INTO channel VALUES (null, %s, null, %s, %s, %s);", (channelId, channelName, groupName, now.strftime("%Y-%m-%d %H:%M:%S")))
                    cursor.execute("SELECT id FROM channel WHERE channelId = %s", (channelId, ))
                    model = cursor.fetchone()
                    channelIdInt = model[0]
                else:
                    cursor.execute("UPDATE channel SET `group` = %s WHERE channelId = %s AND `group` is Null;", (groupName, channelId))
                    channelIdInt = model[0]

                cnum = ranking.find('p', class_='regist').get_text().replace("人", "").replace(",", "")
                vnum = ranking.find('p', class_='play').get_text().replace("回", "").replace(",", "")
                cursor.execute("INSERT INTO channelData VALUES (null, %s, %s, %s, %s);", (channelIdInt, cnum, vnum, now.strftime("%Y-%m-%d %H:%M:%S")))
        db.commit()

    cursor.close()

if __name__ == "__main__":
    main()