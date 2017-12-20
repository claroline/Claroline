import {LocaleGroup} from '#/main/core/data/types/locale/components/form-group.jsx'

const LOCALE_TYPE = 'locale'

import {t} from '#/main/core/translation'

const localeDefinition = {
  meta: {
    type: LOCALE_TYPE,
    creatable: false,
    label: t('locale'),
    description: t('locale_desc')
  },
  parse: (display) => parseFloat(display),
  render: (raw) => t(raw),
  validate: () => undefined,
  components: {
    form: LocaleGroup
  }
}

export {
  LOCALE_TYPE,
  localeDefinition
}
