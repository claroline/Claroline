import {HtmlCell} from '#/main/core/layout/data/types/html/components/table.jsx'

export const HTML_TYPE = 'html'

export const htmlDefinition = {
  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: (value) => typeof value === 'string',
  components: {
    table: HtmlCell
  }
}
