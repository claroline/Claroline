export function getOffsets(element, selection = null) {
  //tinymce has his own selection object so we pass it if we must. It might work with the window one (untested)
  if (!selection) {
    selection = window.getSelection()
  }

  //I saw that on stackoverflow http://stackoverflow.com/questions/42272239/get-the-position-of-a-selected-text-from-html-document
  //This is already a little hack to get the offset inside a particular div
  let range = selection.getRangeAt(0)
  let priorRange = range.cloneRange()
  priorRange.selectNodeContents(element)
  priorRange.setEnd(range.startContainer, range.startOffset)
  let start = priorRange.toString().length
  let end = start + range.toString().length
  //let string = selection.toString()

  const offsets = { start, end }

  const html = element.innerHTML

  //now we can compute the "real" offsets
  let toAdd = getRealOffset(html, offsets.start)

  offsets.trueStart = toAdd + offsets.start

  toAdd = getRealOffset(html, offsets.end)
  //fuck this shit, we have to take into account html stuff here aswell. We can't just use offset.end here
  offsets.trueEnd = toAdd + offsets.end

  return offsets
}

function getRealOffset(html, htmlContentOffset) {
  let i = 0
  let j = 0
  let toAdd = 0
  let forward = 0

  while (i <= htmlContentOffset) {
    //this is the beginning of a tag
    if (html[j] === '<' ) {
      forward = getTillChar(html, j, '>')
    }

    //this is the beginning of the special char !
    if (html[j] === '&' ) {
      forward = getTillChar(html, j, ';')
      forward-- //we still have to add the special char !
    }

    //ADD EVERYTHING !!
    toAdd += forward

    if (forward > 0) {
      j += forward
    } else {
      j++
      i++
    }

    forward = 0
  }

  return toAdd
}

//this function just count the number of character till the one you chose as 'marker'
//I guess there is a way to do it with a regex or something but it's working fine
function getTillChar(html, index, marker) {
  let keepGoing = true
  let length = 0

  for (let i = index; keepGoing === true; i++) {
    if (html[i] === marker) {
      keepGoing = false
    }
    length ++
  }

  return length
}
