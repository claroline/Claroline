import {t} from '#/main/core/translation'

import {FieldsGroup} from '#/main/core/data/form/components/group/fields-group.jsx'

const FIELDS_TYPE = 'fields'

// todo add validation

const fieldsDefinition = {
  meta: {
    type: FIELDS_TYPE,
    creatable: false,
    icon: 'fa fa-fw fa fa-dot',
    label: t('fields'),
    description: t('fields_desc')
  },

  // nothing special to do
  parse: (display) => display,
  // nothing special to do
  render: (raw) => raw,
  validate: () => undefined,
  components: {
    form: FieldsGroup
  }
}

export {
  FIELDS_TYPE,
  fieldsDefinition
}
