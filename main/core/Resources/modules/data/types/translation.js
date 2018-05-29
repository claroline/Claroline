import {trans} from '#/main/core/translation'
import {chain, string} from '#/main/core/validation'

import {TextGroup} from '#/main/core/layout/form/components/group/text-group.jsx'

const TRANSLATION_TYPE = 'translation'

const translationDefinition = {
  meta: {
    type: TRANSLATION_TYPE,
    creatable: false,
    icon: 'fa fa-fw fa fa-language',
    label: trans('translation'),
    description: trans('translation_desc')
  },

  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw, options) => trans(raw, options.placeholders || {}, options.domain),
  validate: (value, options) => chain(value, options, [string]),
  components: {
    form: TextGroup
  }
}

export {
  TRANSLATION_TYPE,
  translationDefinition
}
