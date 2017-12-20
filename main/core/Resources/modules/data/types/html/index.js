import {t} from '#/main/core/translation'

import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'
import {HtmlCell} from '#/main/core/data/types/html/components/table.jsx'

const HTML_TYPE = 'html'

const htmlDefinition = {
  meta: {
    type: HTML_TYPE,
    creatable: true,
    icon: 'fa fa-fw fa fa-code',
    label: t('html'),
    description: t('html_desc')
  },
  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: (value) => typeof value === 'string',
  components: {
    table: HtmlCell,
    form: HtmlGroup
  }
}

export {
  HTML_TYPE,
  htmlDefinition
}
