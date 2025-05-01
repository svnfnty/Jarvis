import speech_recognition as sr
import sys

def main():
    r = sr.Recognizer()
    with sr.Microphone() as source:
        r.adjust_for_ambient_noise(source, duration=0.5)
        try:
            audio = r.listen(source, timeout=3, phrase_time_limit=5)
            try:
                text = r.recognize_google(audio)
                if text.lower().strip() not in ["speak now", "listening"]:
                    print(text.strip())
            except sr.UnknownValueError:
                pass
            except sr.RequestError:
                pass
        except Exception:
            pass

if __name__ == "__main__":
    main()