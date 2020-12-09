
function getFormDataPart(id, tabs) {
  const tabPath = getTabPath(id, tabs)
  let formDataPart = `[${tabPath[0]}]`

  for (let i = 1; i < tabPath.length; ++i) {
    formDataPart += `.children[${tabPath[i]}]`
  }

  return formDataPart
}

function getTabPath(id, tabs, level = 0, indexes = []) {
  const index = tabs.findIndex(s => s.id === id)

  if (index > -1) {
    indexes[level] = index
    indexes.splice(level + 1)

    return indexes
  } else {
    for (let key = 0; key < tabs.length; ++key) {
      if (tabs[key].children.length > 0) {
        indexes[level] = key
        const tabPath = getTabPath(id, tabs[key].children, level + 1, indexes)

        if (tabPath) {
          return tabPath
        }
      }
    }

    return null
  }
}

function getTabParent(id, tabs) {
  const tabPath = getTabPath(id, tabs)

  // remove current
  tabPath.pop()

  if (0 !== tabPath.length) {
    let parent = tabs[tabPath[0]]
    for (let i = 1; i < tabPath.length; i++) {
      parent = parent.children[tabPath[i]]
    }

    return parent
  }

  return null
}

export {
  getFormDataPart,
  getTabPath,
  getTabParent
}
