
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

export {
  isTypeAllowed
}
