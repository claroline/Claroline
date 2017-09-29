
function getPlainText(htmlText) {
  if (htmlText) {
    const tmp = document.createElement('div')
    tmp.innerHTML = htmlText

    return tmp.textContent || tmp.innerText || ''
  }

  return ''
}

export {
  getPlainText
}
