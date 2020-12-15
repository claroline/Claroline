import {trans} from '#/main/app/intl/translation'

import {LocaleInput} from '#/main/app/data/types/locale/components/input'

const dataType = {
  name: 'locale',
  meta: {
    icon: 'fa fa-fw fa-globe',
    label: trans('locale', {}, 'data'),
    description: trans('locale_desc', {}, 'data')
  },
  render: (raw) => trans(raw),
  components: {
    input: LocaleInput
  }
}

export {
  dataType
}
