import {trans} from '#/main/app/intl/translation'

import {LocaleGroup} from '#/main/core/layout/form/components/group/locale-group'

const dataType = {
  name: 'locale',
  meta: {
    label: trans('locale'),
    description: trans('locale_desc')
  },
  render: (raw) => trans(raw),
  components: {
    form: LocaleGroup
  }
}

export {
  dataType
}
