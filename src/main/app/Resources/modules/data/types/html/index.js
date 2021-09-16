import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {chain, string} from '#/main/app/data/types/validators'

import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {HtmlCell} from '#/main/app/data/types/html/components/table'
import {ContentHtml} from '#/main/app/content/components/html'

const dataType = {
  name: 'html',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa fa-code',
    label: trans('html', {}, 'data'),
    description: trans('html_desc', {}, 'data')
  },
  // nothing special to do
  parse: (display) => display,
  render: (raw) => {
    const htmlRendered = React.createElement(ContentHtml, {}, raw)

    return htmlRendered
  },
  validate: (value, options) => chain(value, options, [string]),
  components: {
    table: HtmlCell,
    input: HtmlInput
  }
}

export {
  dataType
}
