import {LocaleGroup} from '#/main/core/layout/form/components/group/locale-group.jsx'

const LOCALE_TYPE = 'locale'

import {trans} from '#/main/core/translation'

const localeDefinition = {
  meta: {
    type: LOCALE_TYPE,
    creatable: false,
    label: trans('locale'),
    description: trans('locale_desc')
  },
  render: (raw) => trans(raw),
  components: {
    form: LocaleGroup
  }
}

export {
  LOCALE_TYPE,
  localeDefinition
}
