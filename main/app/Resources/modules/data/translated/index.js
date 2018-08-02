import {trans} from '#/main/core/translation'
import {getLocale} from '#/main/app/intl/locale'

import {TranslatedGroup} from '#/main/core/layout/form/components/group/translated-group'

const dataType = {
  name: 'translated',
  meta: {
    icon: 'fa fa-fw fa fa-code',
    label: trans('translated'),
    description: trans('translated_desc')
  },
  render: (raw) => raw[getLocale()],
  components: {
    form: TranslatedGroup
  }
}

export {
  dataType
}
