import {trans} from '#/main/app/intl/translation'
import {locale} from '#/main/app/intl/locale'

import {TranslatedInput} from '#/main/app/data/types/translated/components/input'

const dataType = {
  name: 'translated',
  meta: {
    icon: 'fa fa-fw fa fa-code',
    label: trans('translated', {}, 'data'),
    description: trans('translated_desc', {}, 'data')
  },
  render: (raw) => raw[locale()],
  components: {
    input: TranslatedInput
  }
}

export {
  dataType
}
