export const utils = {}

utils.split = (text, solutions, highlight = true) => {
  if (!text) return [{
    word: '#endoftext#',
    position: null,
    text,
    score: null
  }]

  const split = utils.getTextElements(text, solutions).filter(el => el.found)

  //now we can reorder the array by position and split the text accordingly
  split.sort((a, b) =>  a.position - b.position)

  //now we can split the text accordingly
  //This is a big mess of wtf computations but I swear it gives the correct result !
  let currentPosition = 0
  let prevPos = 0
  let prevWordLength = 0

  split.forEach(el => {
    //we keep track of each text element
    el.text = text.substr(0, el.position + el.word.length - currentPosition)
    //now we trim the text
    text = text.substr(el.position + el.word.length - currentPosition)
    currentPosition += (el.position + el.word.length - prevPos - prevWordLength)
    prevPos = el.position
    prevWordLength = el.word.length
  })

  //now we highlight the text if required
  if (highlight) {
    split.forEach(el => {
      let regexFlag = 'g'
      if (!el.caseSensitive) regexFlag += 'i'
      const regex = new RegExp('(\\b' + el.word + '\\b)', regexFlag)
      const icon = el.score > 0 ? 'fa fa-fw fa-check': 'fa fa-fw fa-times'
      const replacer = `<strong><span class="${icon}"></span>$1</strong>`
      el.text = el.text.replace(regex, replacer)
    })
  }

  //I want to remember the last element of the text so I add it as well to the array
  split.push({
    word: '#endoftext#',
    position: null,
    text,
    score: null
  })

  return split
}

utils.getTextElements = (text, solutions) => {
  if (!text) {
    return []
  }
  const data = []

  //first we find each occurence of a given word
  solutions.forEach(solution => {
    const word = solution.text
    let regexFlag = 'g'
    if (!solution.caseSensitive) regexFlag += 'i'
    const regex = new RegExp('\\b' + word + '\\b', regexFlag)
    //console.log(regex)
    const position = text.search(regex)
    data.push({
      caseSensitive: solution.caseSensitive,
      word,
      position,
      score: solutions.find(el => el.text === word).score,
      feedback: solutions.find(el => el.text === word).feedback,
      found: position > -1
    })
  })

  return data
}
