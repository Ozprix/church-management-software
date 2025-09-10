<template>
  <div class="voice-search">
    <button 
      @click="toggleVoiceSearch" 
      class="voice-search-button"
      :class="{ 'recording': isListening }"
      :title="$t(isListening ? 'search.stopVoiceSearch' : 'search.startVoiceSearch')"
    >
      <i class="fas" :class="isListening ? 'fa-stop' : 'fa-microphone'"></i>
    </button>
    
    <div v-if="isListening" class="voice-search-indicator">
      <div class="voice-waves">
        <div class="wave wave1"></div>
        <div class="wave wave2"></div>
        <div class="wave wave3"></div>
      </div>
      <div class="voice-status">{{ $t('search.listening') }}...</div>
    </div>
    
    <div v-if="errorMessage" class="voice-error">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from '../../services/i18nService';

// Props
const props = defineProps({
  language: {
    type: String,
    default: 'en'
  }
});

// Get i18n service
const i18n = useI18n();

// Emits
const emit = defineEmits(['result']);

// Component state
const isListening = ref(false);
const errorMessage = ref('');
const recognition = ref(null);

// Initialize speech recognition
onMounted(() => {
  initSpeechRecognition();
});

// Clean up on unmount
onBeforeUnmount(() => {
  stopRecognition();
});

// Initialize speech recognition
const initSpeechRecognition = () => {
  try {
    // Check browser support
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
      errorMessage.value = i18n.t('search.voiceSearchNotSupported');
      return;
    }
    
    // Create recognition instance
    recognition.value = new SpeechRecognition();
    
    // Configure recognition
    recognition.value.continuous = false;
    recognition.value.interimResults = false;
    recognition.value.lang = getLanguageCode(props.language);
    
    // Set up event handlers
    recognition.value.onresult = handleResult;
    recognition.value.onerror = handleError;
    recognition.value.onend = handleEnd;
    
    errorMessage.value = '';
  } catch (error) {
    console.error('Error initializing speech recognition:', error);
    errorMessage.value = i18n.t('search.voiceSearchError');
  }
};

// Toggle voice search
const toggleVoiceSearch = () => {
  if (isListening.value) {
    stopRecognition();
  } else {
    startRecognition();
  }
};

// Start voice recognition
const startRecognition = () => {
  if (!recognition.value) {
    initSpeechRecognition();
  }
  
  if (recognition.value) {
    try {
      // Update language in case it changed
      recognition.value.lang = getLanguageCode(props.language);
      
      // Start listening
      recognition.value.start();
      isListening.value = true;
      errorMessage.value = '';
    } catch (error) {
      console.error('Error starting speech recognition:', error);
      errorMessage.value = i18n.t('search.voiceSearchError');
      isListening.value = false;
    }
  }
};

// Stop voice recognition
const stopRecognition = () => {
  if (recognition.value && isListening.value) {
    try {
      recognition.value.stop();
    } catch (error) {
      console.error('Error stopping speech recognition:', error);
    }
    
    isListening.value = false;
  }
};

// Handle recognition result
const handleResult = (event) => {
  const result = event.results[0][0].transcript;
  
  if (result) {
    // Emit result to parent
    emit('result', result);
    
    // Stop listening
    stopRecognition();
  }
};

// Handle recognition error
const handleError = (event) => {
  console.error('Speech recognition error:', event.error);
  
  switch (event.error) {
    case 'not-allowed':
      errorMessage.value = i18n.t('search.microphoneNotAllowed');
      break;
    case 'no-speech':
      errorMessage.value = i18n.t('search.noSpeechDetected');
      break;
    default:
      errorMessage.value = i18n.t('search.voiceSearchError');
  }
  
  isListening.value = false;
};

// Handle recognition end
const handleEnd = () => {
  isListening.value = false;
};

// Map language code to speech recognition language code
const getLanguageCode = (language) => {
  const languageMap = {
    'en': 'en-US',
    'es': 'es-ES',
    'fr': 'fr-FR',
    'de': 'de-DE',
    'pt': 'pt-BR',
    'zh': 'zh-CN'
  };
  
  return languageMap[language] || 'en-US';
};
</script>

<style scoped>
.voice-search {
  position: relative;
  display: inline-block;
}

.voice-search-button {
  background: none;
  border: none;
  cursor: pointer;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  color: #6b7280;
  background-color: #f3f4f6;
}

.voice-search-button:hover {
  background-color: #e5e7eb;
  color: #4b5563;
}

.voice-search-button.recording {
  background-color: #ef4444;
  color: white;
  animation: pulse 1.5s infinite;
}

.voice-search-indicator {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 8px;
  padding: 8px 12px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  display: flex;
  align-items: center;
  z-index: 10;
  white-space: nowrap;
}

.voice-waves {
  display: flex;
  align-items: center;
  margin-right: 8px;
}

.wave {
  width: 3px;
  height: 12px;
  margin: 0 1px;
  border-radius: 1px;
  background-color: #ef4444;
  animation: wave 1s infinite ease-in-out;
}

.wave1 {
  animation-delay: 0s;
}

.wave2 {
  animation-delay: 0.2s;
}

.wave3 {
  animation-delay: 0.4s;
}

.voice-status {
  font-size: 0.875rem;
  color: #1f2937;
}

.voice-error {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 8px;
  padding: 8px 12px;
  background-color: #fee2e2;
  color: #b91c1c;
  border-radius: 8px;
  font-size: 0.75rem;
  white-space: nowrap;
  z-index: 10;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
  }
}

@keyframes wave {
  0%, 100% {
    height: 6px;
  }
  50% {
    height: 12px;
  }
}

/* Dark mode support */
:global(.dark) .voice-search-button {
  color: #9ca3af;
  background-color: #374151;
}

:global(.dark) .voice-search-button:hover {
  background-color: #4b5563;
  color: #f3f4f6;
}

:global(.dark) .voice-search-indicator {
  background-color: #1f2937;
}

:global(.dark) .voice-status {
  color: #f3f4f6;
}

:global(.dark) .voice-error {
  background-color: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}
</style>
