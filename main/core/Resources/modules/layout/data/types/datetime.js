
export const DATETIME_TYPE = 'datetime'

export const datetimeDefinition = {
  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: (value) => typeof value === 'string',
  components: {
    form: null,
    table: null,
    search: null
  }
}
