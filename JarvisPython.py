import speech_recognition as sr
import sys
import logging

logging.basicConfig(level=logging.INFO, format='%(asctime)s - JARVIS - %(levelname)s - %(message)s')

def main():
    logging.info("Initializing Jarvis voice recognition module...")
    r = sr.Recognizer()
    with sr.Microphone() as source: 
        r.adjust_for_ambient_noise(source, duration=0.5)
        logging.info("Microphone calibrated for ambient noise.")
        try:
            logging.info("Listening for your command...")
            audio = r.listen(source, timeout=3, phrase_time_limit=5)
            logging.info("Audio input received.")
            try:
                text = r.recognize_google(audio)
                if text.lower().strip() not in ["speak now", "listening"]:
                    logging.info(f"Recognized command: {text.strip()}")
                    print(text.strip())
            except sr.UnknownValueError:
                logging.warning("I couldn't understand what you said.")
            except sr.RequestError as e:
                logging.error(f"Speech recognition service error: {e}")
        except Exception as e:
            logging.error(f"An unexpected error occurred: {e}")

if __name__ == "__main__":
    main()