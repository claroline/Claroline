import {trans} from '#/main/app/intl/translation'

import {TemplateTypeDisplay} from '#/main/core/data/types/template-type/components/display'
import {TemplateTypeGroup} from '#/main/core/data/types/template-type/components/group'

const dataType = {
  name: 'template_type',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-file-alt',
    label: trans('template_type', {}, 'template'),
    description: trans('template_type_desc', {}, 'template')
  },
  components: {
    details: TemplateTypeDisplay,
    form: TemplateTypeGroup
  }
}

export {
  dataType
}
