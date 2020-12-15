
function addClasses(element, ...args) {
  // only add missing classes
  const toAdd = args.filter(className => !element.className || -1 === element.className.indexOf(className))

  // grab existing classes
  if (element.className) {
    toAdd.unshift(element.className)
  }

  element.className = toAdd.join(' ')
}

function removeClasses(element, ...args) {
  let classes = element.className || ''

  // NB. like this it will only remove one occurrence of class name
  args.map(className => {
    classes = classes.replace(className, '')
  })

  element.className = classes.trim()
}

export {
  addClasses,
  removeClasses
}
