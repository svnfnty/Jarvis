<?php

class OfflineJarvis {
    private $isSpeaking = false;
    private $systemPrompt = "You are JARVIS, a highly intelligent and polite AI assistant. Speak in a formal yet friendly tone, and always aim to assist the user efficiently.";

    public function listenToMicrophone() {
        // Wait if currently speaking
        while ($this->isSpeaking) {
            usleep(100000); // 100ms delay
        }
          
        $output = [];
        $command = 'python JarvisPython.py 2>&1';
        exec($command, $output, $return_var);
        
        // Enhanced filtering
        $filtered = array_filter($output, function($line) {
            $cleanLine = trim($line);
            return !empty($cleanLine) &&
                   !preg_match('/^(LOG|DEBUG|Speak now|Initializing|Adjusting)/i', $cleanLine) &&
                   strlen($cleanLine) > 3; // Minimum 3 characters
        });
        
        return !empty($filtered) ? trim(implode(" ", $filtered)) : '';
    }
    
    public function talkToOllama($prompt) {
        $url = 'http://localhost:11434/api/generate';
        $data = [
            'model' => 'gemma:2b',
            'prompt' => $this->systemPrompt . "\nUser: " . $prompt,
            'stream' => false
        ];
        
        try {
            $options = [
                'http' => [
                    'header' => "Content-Type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode($data),
                    'timeout' => 100 // 10 second timeout
                ]
            ];
            
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            
            if ($result === FALSE) {
                error_log("Failed to connect to Ollama API.");
                return "I'm having trouble connecting to my AI engine.";
            }
            
            $response = json_decode($result, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decoding error: " . json_last_error_msg());
                return "I received an invalid response from the AI engine.";
            }
            
            return $response['response'] ?? "I didn't get a proper response.";
        } catch (Exception $e) {
            error_log("Exception in talkToOllama: " . $e->getMessage());
            return "My AI service is currently unavailable.";
        }
    }
    
    public function speak($text) {
        if (empty($text)) return false;

        // Special handling for code blocks
        $text = preg_replace_callback('/```.*?\n(.*?)```/s', function($matches) {
            return 'code block: ' . str_replace(["'", "\n"], ["'", " "], $matches[1]);
        }, $text);

        // General text cleaning
        $text = htmlspecialchars_decode($text);
        $text = preg_replace('/[^\p{L}\p{N}\s.,!?\-]/u', ' ', $text);
        $text = addslashes($text);

        $psScript = <<<EOT
            \$ErrorActionPreference = "Stop"
            try {
                Add-Type -AssemblyName System.Speech
                \$speak = New-Object System.Speech.Synthesis.SpeechSynthesizer
                
                # Try preferred voice, fallback to any available
                \$voices = \$speak.GetInstalledVoices() | % { \$_.VoiceInfo.Name }
                if ('Microsoft David Desktop' -in \$voices) {
                    \$speak.SelectVoice('Microsoft David Desktop')
                }
                
                \$speak.Rate = 1
                \$speak.Volume = 100
                \$speak.Speak('$text')
                exit 0
            } catch {
                Write-Output \$_.Exception.Message
                exit 1
            }
        EOT;
        // Execute via temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'tts_') . '.ps1';
        file_put_contents($tempFile, $psScript);
        $output = shell_exec("powershell -ExecutionPolicy Bypass -File \"$tempFile\" 2>&1");
        unlink($tempFile);

        return $output === null;
    }
    
    public function processCommand($command) {
        $command = strtolower(trim($command));
        
        // System commands
        switch ($command) {
            case 'exit':
            case 'quit':
            case 'shutdown':
            case 'shut down':
                $this->speak("Shutting down. Goodbye, my friend.");
                return ['action' => 'exit', 'response' => "System shutting down. Farewell."];
                
            case 'time':
                $time = date('g:i a');
                return ['response' => "The current time is $time. How else may I assist you?"];
                
            case 'date':
                $date = date('l, F jS');
                return ['response' => "Today is $date. Anything else you'd like to know?"];
                
            default:
                return null;
        }
    }
}

if (PHP_SAPI === 'cli') {
    echo "OFFLINE JARVIS Initializing...\n";
    
    $jarvis = new OfflineJarvis();
    $jarvis->speak("System ready. How may I assist you?");
    
    while (true) {
        echo "\n[READY] Listening...\n";
        $heard = $jarvis->listenToMicrophone();
        
        if (!empty($heard)) {
            echo "USER: $heard\n";
            
            // First check for system commands
            $commandResponse = $jarvis->processCommand($heard);
            
            if ($commandResponse !== null) {
                if (isset($commandResponse['action']) && $commandResponse['action'] === 'exit') {
                    echo "JARVIS: {$commandResponse['response']}\n";
                    break;
                }
                echo "JARVIS: {$commandResponse['response']}\n";
                $jarvis->speak($commandResponse['response']);
            } else {
                // Process with Ollama
                $reply = $jarvis->talkToOllama($heard);
                echo "JARVIS: $reply\n";
                $jarvis->speak($reply);
            }
            
            // Brief pause between interactions
            sleep(1);
        } else {
            echo "I didn't catch that. Please try again.\n";
        }
    }
}