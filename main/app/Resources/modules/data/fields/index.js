import {trans} from '#/main/core/translation'

import {FieldsGroup} from '#/main/app/data/fields/components/group'

// todo add validation

const dataType = {
  name: 'fields',
  meta: {
    icon: 'fa fa-fw fa-dot',
    label: trans('fields'),
    description: trans('fields_desc')
  },
  components: {
    form: FieldsGroup
  }
}

export {
  dataType
}
