import {t} from '#/main/core/translation'

function copyToClipboard(text) {
  let textArea = document.createElement('textarea')
  textArea.style.position = 'fixed'
  textArea.style.top = 0
  textArea.style.left = 0
  textArea.style.width = '1px'
  textArea.style.height = '1px'
  textArea.style.padding = 0
  textArea.style.border = 'none'
  textArea.style.outline = 'none'
  textArea.style.boxShadow = 'none'
  textArea.style.background = 'transparent'
  textArea.style.zIndex = -1000
  textArea.value = text

  document.body.appendChild(textArea)

  textArea.select()

  try {
    document.execCommand('copy')
  } catch (error) {
    alert(t('copy_to_clipboard_error'))
  }

  document.body.removeChild(textArea)
  alert(t('copy_to_clipboard_success'))
}

// export for ES6 use
export {
  copyToClipboard
}
