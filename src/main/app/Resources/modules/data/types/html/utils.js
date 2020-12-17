function mediaSrcExtractor(tag, srcContent, srcs) {
  const mainSource = !srcContent ? '' : `src="${srcContent}"`
  let ret = `[${tag} ${mainSource}`

  if (srcs) {
    const sources = srcs.match(/src=['"]([^'"]+)['"]/g)
    sources.forEach(source => ret += ` src="${source}"`)
  }
  ret += ']'

  return ret
}

function getPlainText(htmlText, preserveMedia = false) {
  if (htmlText) {
    let processedHtml = htmlText

    if (preserveMedia) {
      processedHtml = processedHtml.replace(
        /<(img|embed)([^>]+src=['"]([^'"]+)['"])*[^/>]*\/?>/i,
        '[$1 src="$3"]'
      )
      processedHtml = processedHtml.replace(
        /<(video|audio)([^>]+src=['"]([^'"]+)['"])*[^/>]*\/?>([\s\S]*)<\/\1>/i,
        (matches, tag, src, srcContent, srcs) => mediaSrcExtractor(tag, srcContent, srcs)
      )
    }
    const tmp = document.createElement('div')
    tmp.innerHTML = processedHtml

    return tmp.textContent || tmp.innerText || ''
  }

  return ''
}

export {
  getPlainText
}
