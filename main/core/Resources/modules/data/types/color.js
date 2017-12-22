
const COLOR_TYPE = 'color'

const colorDefinition = {
  meta: {
    type: COLOR_TYPE
  },
  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: (value) => typeof value === 'string'
}

export {
  COLOR_TYPE,
  colorDefinition
}
