# Word Wheel Breaker
# Run to solve the daily World Wheel and add the
# group name to the high score table

import time

from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By

HIGH_SCORE_NAME = "LASHKRS"

print("Begin...")
EXPRESS_URL = "https://games.express.co.uk/game/pa-express-word-wheel"
chrome_options = Options()
chrome_options.add_argument('--no-sandbox')
chrome_options.add_argument('--disable-dev-shm-usage')

driver = webdriver.Chrome(options=chrome_options)


def start_game():
    # open browser and navigate to Word Wheel
    driver.get(EXPRESS_URL)

    # Close cookie notices 🍪
    driver.find_element(By.ID, "popup-play-button").click()
    time.sleep(1)
    driver.find_element(By.ID, "onetrust-accept-btn-handler").click()

    # Switch to game frame
    driver.switch_to.frame("pa-embedded-element")

    # Locate tutorial and close
    tutorial = driver.find_element(By.ID, "puzzle-tutorial")
    # time.sleep(0.5)
    tutorial.find_element(By.CLASS_NAME, "close-btn").click()


def play_again(words):
    driver.refresh()

    # Switch to game frame
    driver.switch_to.frame("pa-embedded-element")

    # Locate tutorial and close
    tutorial = driver.find_element(By.ID, "puzzle-tutorial")
    time.sleep(0.5)
    tutorial.find_element(By.CLASS_NAME, "close-btn").click()

    for word in words:
        input_word(word)

    leaderboard(HIGH_SCORE_NAME)
    time.sleep(1)


def input_word(word):
    # Inserts word into text box
    driver.find_element(By.ID, "word-input").send_keys(word)

    # Clicks enter
    driver.find_element(By.CLASS_NAME, "btn_submit").click()


def find_letters():
    letter_list = []

    # appends letters to letter_list with middle letter at the end
    for i in range(1, 9):
        letter_list.append((driver.find_element(By.XPATH, ("//*[@id='word-wheel']/span[" + str(i) + "]")).get_attribute(
            "data-letter")).lower())

    # Finds centre letter
    centre_letter = driver.find_element(By.XPATH, ("//*[@id='word-wheel']/span[9]")).get_attribute("data-letter")

    # Returns tuple of letters around centre and centre letter.
    return letter_list, centre_letter.lower()


def validate(word, centre, otherletters):
    # checks if centre is in the word.
    # returns False if not in the word

    if len(word) < 4:
        return False

    if centre not in word:
        return False

    # loops through word, checks if word contains letters not in otherletters
    for letter in word:
        if letter not in otherletters:
            return False

    return True


# logic error! The variable "letters" was used more than once
# parameter name "letters" replaced with "puzzle_letters"
def word_checker(puzzle_letters):
    # opens english4.txt containing every 4-9 letter word in the dictionary
    wordlist = open("english4.txt", "r")

    # Gets other letters
    letters = puzzle_letters[0]

    is_leaderboard_visible = False

    # Gets centre letter
    centre_letter = puzzle_letters[1]

    # Assigns the lines of the wordlist to the variable words.
    words = wordlist.readlines()
    words.sort()
    words = set(words)
    current_number_of_found = 0

    list_correct = []

    # Loops for every word on each line.
    for word in words:
        # Remove whitespace.
        currentword = word.strip()
        # Calls validate variable to check if current word is valid and checks if the game has ended.

        if validate(currentword, centre_letter, letters) and is_leaderboard_visible == False:
            # print("testing", word)

            # Inputs word and presses enter.
            input_word(currentword)

            cnof = int(driver.find_element(By.XPATH, "//*[@id='complete-words-wrapper']/div[1]/span").text)

            if current_number_of_found < cnof:
                list_correct.append(currentword)

            # Checks if game has ended.
            is_leaderboard_visible = driver.find_element(By.ID, "scoreForm").is_displayed()

            current_number_of_found = cnof
    return list_correct


def leaderboard(name):
    # Finds score info
    scoreinfo = "\n" + driver.find_element(By.CLASS_NAME, "score").text
    print(scoreinfo)
    # Inserts name into box
    driver.find_element(By.ID, "txtName").send_keys(name)
    # Click enter
    driver.find_element(By.ID, "submitScoreButtonID").click()
    time.sleep(2)

    # Returns


# main

# open site and close dialog boxes
start_game()

# Finds and enters possible words.
words_found = word_checker(find_letters())
print(words_found)

# Retrieves game information and inserts name.


# do it again
print("Playing again...")

for grandpas in range(30):
    play_again(words_found)

driver.quit()
print("END.")

