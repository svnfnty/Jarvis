<?php
class VoiceAssistant {
    private $isSpeaking = false;
    private $apiToken;
    private $userName;
    
    public function __construct($apiToken, $userName = '') {
        $this->apiToken = $apiToken;
        $this->userName = $userName ?: 'sir';
    }
    
    public function callDeepSeek($prompt) {
    $url = "https://openrouter.ai/api/v1/chat/completions";
    $data = json_encode([
        "model" => "deepseek/deepseek-v3-base:free",
        "messages" => [
            ["role" => "system", "content" => "You are JARVIS, a helpful AI assistant. Respond conversationally and keep answers brief. Don't add emojis."],
            ["role" => "user", "content" => $prompt]
        ],
        "temperature" => 0.7,
        "max_tokens" => 150
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$this->apiToken}",
        "Content-Type: application/json",
        "HTTP-Referer: https://yourdomain.com", // Required by OpenRouter
        "X-Title: JARVIS Assistant" // Required by OpenRouter
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return "I encountered an error: " . $error;
    }
    curl_close($ch);

    $responseData = json_decode($response, true);
    
    if (isset($responseData['error'])) {
        return "API Error: " . $responseData['error']['message'];
    }

    return $responseData['choices'][0]['message']['content'] ?? "I didn't quite get that. Could you repeat?";
}

    public function listenToMicrophone() {
        while ($this->isSpeaking) {
            usleep(100000);
        }

        $output = [];
        $pythonPath = '"C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python313\\python.exe"';
        $scriptPath = '"C:\\Users\\Administrator\\Desktop\\JARVIS\\listen_once.py"';
        $command = "$pythonPath $scriptPath 2>&1";
        
        exec($command, $output, $return_var);

        // Enhanced filtering
        $filtered = array_filter($output, function($line) {
            $line = trim($line);
            return !empty($line) && 
                   !preg_match('/^(LOG|ERROR|DEBUG|Speak now|Initializing|Adjusting)/i', $line) &&
                   strlen($line) > 3; // Minimum 3 characters to be valid
        });

        return !empty($filtered) ? trim(implode(" ", $filtered)) : '';
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
            
            \$speak.Rate = 2
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

    private function executeCommand($command) {
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        
        return ($return_var === 0) 
            ? "Done, {$this->userName}." 
            : "I couldn't complete that action, {$this->userName}.";
    }

    public function processCommand($command) {
        $command = strtolower(trim($command));
        
        if (preg_match('/^(open|start) (.+)/', $command, $matches)) {
            $app = $matches[2];
            switch ($app) {
                case 'edge': case 'microsoft edge': return $this->executeCommand('start msedge');
                case 'chrome': case 'google chrome': return $this->executeCommand('start chrome');
                case 'notepad': return $this->executeCommand('start notepad');
                case 'calculator': return $this->executeCommand('start calc');
                default: return "I can't open {$app}, {$this->userName}.";
            }
        }
        
        switch ($command) {
            case 'shutdown': case 'shut down':
                $this->speak("Shutting down. Goodbye, {$this->userName}.");
                exec('shutdown /s /t 0');
                exit;
            case 'restart': case 'reboot':
                $this->speak("Restarting. See you soon, {$this->userName}.");
                exec('shutdown /r /t 0');
                exit;
            case 'lock': case 'lock computer':
                $this->speak("Locking system, {$this->userName}.");
                exec('rundll32.exe user32.dll,LockWorkStation');
                exit;
            case 'time': return "The time is " . date('g:i a');
            case 'date': return "Today is " . date('l, F jS');
            case 'who are you': return "I'm JARVIS, your assistant, {$this->userName}.";
            case 'hello': case 'hi': case 'hey': 
                return ["Hello", "Hi", "Greetings"][rand(0,2)] . ", {$this->userName}. How can I help?";
            default: return null;
        }
    }
}

if (PHP_SAPI === 'cli') {
    echo "JARVIS Initializing...\n";

    // At the start of your CLI section in jarvis2o.php
        $config = parse_ini_file('config.ini', true);
        $apiToken = $config['api']['token'] ?? '';
    
    $assistant = new VoiceAssistant(
        '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',
        'Boss'
    );
    
    $assistant->speak("System ready. How may I assist you today?");
    
    while (true) {
        echo "\n[LISTENING] ";
        $heard = $assistant->listenToMicrophone();
        
        if (!empty($heard) && !preg_match('/speak now/i', $heard)) {
            echo "USER: $heard\n";
            
            $response = $assistant->processCommand($heard) 
                      ?? $assistant->callDeepSeek($heard);
            
            echo "JARVIS: $response\n";
            $assistant->speak($response);
            
            sleep(1);
        } else {
            if (!empty($heard)) {
                echo "Ignoring system message\n";
            } else {
                echo "No valid input detected\n";
            }
        }
    }
}