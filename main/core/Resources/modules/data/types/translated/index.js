//currently, a copy of the html field.
//it will be improved later

import {t} from '#/main/core/translation'
import {getLocale} from '#/main/core/intl/locale'

import {TranslatedGroup} from '#/main/core/layout/form/components/group/translated-group.jsx'

const TRANSLATED_TYPE = 'translated'

const translatedDefinition = {
  meta: {
    type: TRANSLATED_TYPE,
    creatable: false,
    icon: 'fa fa-fw fa fa-code',
    label: t('translated'),
    description: t('translated_desc')
  },
  // nothing special to do
  //parse: (display) => raw[currentLocale],
  // nothing special to do
  render: (raw) => raw[getLocale()],
  //validate: (value) => typeof value === 'string',
  components: {
    form: TranslatedGroup
  }
}

export {
  TRANSLATED_TYPE,
  translatedDefinition
}
