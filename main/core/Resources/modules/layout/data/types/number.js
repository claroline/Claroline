
export const NUMBER_TYPE = 'number'

export const numberDefinition = {
  // nothing special to do
  parse: (display) => parseFloat(display),
  // nothing special to do
  render: (raw) => raw,
  validate: (value) => !isNaN(parseFloat(value)) && isFinite(value),
  components: {
    form: null,
    table: null
  }
}
