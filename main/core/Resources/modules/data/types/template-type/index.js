import {trans} from '#/main/app/intl/translation'

import {TemplateTypeDisplay} from '#/main/core/data/types/template-type/components/display'
import {TemplateTypeInput} from '#/main/core/data/types/template-type/components/input'

const dataType = {
  name: 'template_type',
  meta: {
    icon: 'fa fa-fw fa fa-file-alt',
    label: trans('template_type', {}, 'data'),
    description: trans('template_type_desc', {}, 'data')
  },
  components: {
    details: TemplateTypeDisplay,
    input: TemplateTypeInput
  }
}

export {
  dataType
}
