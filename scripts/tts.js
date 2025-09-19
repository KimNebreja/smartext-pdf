document.addEventListener('DOMContentLoaded', () => {
  speechSynthesis.cancel()

  const playBtn = document.getElementById('ttsPlayBtn')
  const pauseBtn = document.getElementById('ttsPauseBtn')
  const stopBtn = document.getElementById('ttsStopBtn')
  const speedInput = document.getElementById('ttsSpeed')
  const speedValue = document.getElementById('speedValue')
  const voiceSelect = document.getElementById('ttsVoice')
  const progressBar = document.getElementById('ttsProgress')
  const currentTimeEl = document.getElementById('currentTime')
  const totalTimeEl = document.getElementById('totalTime')

  const revisedTextContainer = document.getElementById('revisedText')

  let utterance
  let isPaused = false
  let voices = []
  let startTime
  let totalEstimatedTime = 0

  function populateVoices() {
    voices = speechSynthesis.getVoices()
    voiceSelect.innerHTML = ''
    voices.forEach((voice, index) => {
      const option = document.createElement('option')
      option.value = index
      option.textContent = `${voice.name} (${voice.lang})${
        voice.default ? ' [default]' : ''
      }`
      voiceSelect.appendChild(option)
    })
  }

  function setActiveButton(activeBtn) {
    ;[playBtn, pauseBtn, stopBtn].forEach((btn) => {
      btn.classList.remove('btn-primary')
      btn.classList.add('btn-secondary')
    })

    if (activeBtn) {
      activeBtn.classList.remove('btn-secondary')
      activeBtn.classList.add('btn-primary')
    }
  }

  speechSynthesis.onvoiceschanged = populateVoices
  populateVoices()

  function updateSpeedDisplay() {
    speedValue.textContent = speedInput.value + 'x'
  }

  updateSpeedDisplay()
  speedInput.addEventListener('input', updateSpeedDisplay)

  function speakText(text) {
    if (!text.trim()) return

    utterance = new SpeechSynthesisUtterance(text)
    utterance.rate = parseFloat(speedInput.value)
    utterance.voice = voices[voiceSelect.value]

    const wordCount = text.trim().split(/\s+/).length
    const estimatedWordDuration = 600 / utterance.rate
    totalEstimatedTime = wordCount * estimatedWordDuration
    totalTimeEl.textContent = (totalEstimatedTime / 1000).toFixed(2) + 's'

    startTime = Date.now()

    utterance.onend = () => {
      stopTTS()
    }

    speechSynthesis.speak(utterance)
  }

  function updateProgress() {
    const elapsed = Date.now() - startTime
    const percentage = Math.min((elapsed / totalEstimatedTime) * 100, 100)
    progressBar.style.width = percentage + '%'
    currentTimeEl.textContent = (elapsed / 1000).toFixed(2) + 's'
  }

  function stopTTS() {
    speechSynthesis.cancel()
    utterance = null
    isPaused = false
    progressBar.style.width = '0%'
    currentTimeEl.textContent = '0:00'
    pauseBtn.disabled = true
    stopBtn.disabled = true
  }

  playBtn.addEventListener('click', () => {
    if (isPaused) {
      speechSynthesis.resume()
      isPaused = false
    } else {
      const text = revisedTextContainer.innerText
      speakText(text)
    }
    pauseBtn.disabled = false
    stopBtn.disabled = false
    setActiveButton(playBtn)
  })

  pauseBtn.addEventListener('click', () => {
    if (speechSynthesis.speaking && !speechSynthesis.paused) {
      speechSynthesis.pause()
      isPaused = true
       setActiveButton(pauseBtn)
    }
  })

  stopBtn.addEventListener('click', stopTTS)

  setInterval(() => {
    if (utterance && speechSynthesis.speaking && !speechSynthesis.paused) {
      updateProgress()
       setActiveButton(stopBtn)
    }
  }, 100)
})
