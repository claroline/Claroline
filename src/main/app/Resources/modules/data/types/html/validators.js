
function isHtmlEmpty(html, allowedTags = ['img', 'audio', 'iframe', 'video']) {
  if (!html) {
    return true
  }

  const wrapper = document.createElement('div')
  wrapper.innerHTML = html.trim()

  return !(wrapper.textContent || allowedTags.some((tag) => {
    return html.indexOf(tag) >= 0
  }))
}

export {
  isHtmlEmpty
}
