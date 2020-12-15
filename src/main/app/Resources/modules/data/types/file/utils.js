
function isTypeAllowed(type, allowedTypes = []) {
  let isAllowed = allowedTypes.length === 0

  if (!isAllowed) {
    const regex = new RegExp(type, 'gi')
    allowedTypes.forEach(t => {
      if (t.match(regex)) {
        isAllowed = true
      }
    })
  }

  return isAllowed
}

function getType(mimeType) {
  const typeParts = mimeType.split('/')
  let type = 'file'

  if (typeParts[0] && ['image', 'audio', 'video'].indexOf(typeParts[0]) > -1) {
    type = typeParts[0]
  } else if (typeParts[1]) {
    type = typeParts[1]
  }

  return type
}

export {
  isTypeAllowed,
  getType
}
