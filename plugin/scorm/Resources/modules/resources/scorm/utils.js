function flattenScos(scos) {
  function flatten(sco) {
    const children = sco.children
    const flatSco = Object.assign({}, sco)
    delete flatSco.children

    let flattened = [flatSco]

    if (children) {
      children.map(child => {
        flattened = flattened.concat(flatten(child))
      })
    }

    return flattened
  }

  return scos.reduce((acc, sco) => {
    acc = acc.concat(flatten(sco))

    return acc
  }, [])
}

function getFirstOpenableSco(scos) {
  for (let i = 0; i < scos.length; ++i) {
    if (scos[i].data.entryUrl) {
      return scos[i]
    }
  }

  return scos[0]
}

function generateSummary(scos, callback) {
  return scos.map(sco => ({
    label: sco.data.title,
    type: 'callback',
    callback: () => {
      if (sco.data.entryUrl) {
        callback(sco)
      }
    },
    children: sco.children && sco.children.length > 0 ? generateSummary(sco.children, callback) : []
  }))

}

export {
  flattenScos,
  getFirstOpenableSco,
  generateSummary
}
