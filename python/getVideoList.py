import configparser
import traceback
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
import chromedriver_binary
import time
import datetime
import mysql.connector

ini = configparser.ConfigParser()
ini.read('./config.ini', 'UTF-8')
db=mysql.connector.connect(host=ini['db_info']['host'], user=ini['db_info']['user'], password=ini['db_info']['password'], port=ini['db_info']['port'])

url = "https://holodex.net/home"

def main():
    options = Options()
    options.add_argument('--headless')
    driver = webdriver.Chrome(options=options)

    try:
        driver.get(url)

        # view vtuber list all
        time.sleep(1)
        driver.find_element_by_class_name("nav-title").click()
        time.sleep(1)
        driver.find_elements_by_class_name("v-sheet")[7].find_element_by_class_name("v-list-item").click()
        time.sleep(3)

        # connect
        cursor=db.cursor()
        cursor.execute("USE vtuber_database")
        db.commit()

        values = driver.find_element_by_class_name("video-row").find_elements_by_class_name("video-card")
        now = datetime.datetime.now()
        for videoCard in values:
            channelName = videoCard.find_element_by_class_name("channel-name").text
            channelId = videoCard.find_element_by_class_name("channel-name").find_element_by_tag_name("a").get_attribute("href").split("/")[4]
            videoTitle = videoCard.find_element_by_class_name("video-card-title").get_attribute("title")
            videoId = videoCard.get_attribute("href").split("/")[4]
            cursor.execute("SELECT id FROM channel WHERE channelId = %s", (channelId, ))
            model = cursor.fetchone()
            if model == None:
                cursor.execute("INSERT INTO channel VALUES (null, %s, null, %s, %s, %s);", (channelId, channelName, None, now.strftime("%Y-%m-%d %H:%M:%S")))
            else:
                cursor.execute("UPDATE channel SET channelName = %s WHERE id = %s;", (channelName, model[0]))

            cursor.execute("SELECT id FROM video WHERE videoId = %s", (videoId, ))
            model = cursor.fetchone()
            if model == None:
                cursor.execute("INSERT INTO video (id, channelId, videoId, videoName, isAlive, updatedAt) VALUES (null, %s, %s, %s, %s, %s);", (channelId, videoId, videoTitle, 1, now.strftime("%Y-%m-%d %H:%M:%S")))
            else:
                cursor.execute("UPDATE video SET updatedAt = %s, isAlive = 1 WHERE id = %s;", (now.strftime("%Y-%m-%d %H:%M:%S"), model[0]))

        db.commit()

        # update old video list
        cursor.execute('UPDATE video SET isAlive = 0 WHERE updatedAt < %s', (now.strftime("%Y-%m-%d %H:%M:%S"), ))
        db.commit()
        cursor.close()
    finally:
        driver.quit()

if __name__ == "__main__":
    main()