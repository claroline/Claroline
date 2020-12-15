import {trans} from '#/main/app/intl/translation'

import {TemplateTypeDisplay} from '#/main/core/data/types/template-type/components/display'
import {TemplateTypeInput} from '#/main/core/data/types/template-type/components/input'
import {TemplateTypeCell} from '#/main/core/data/types/template-type/components/cell'
import {TemplateTypeFilter} from '#/main/core/data/types/template-type/components/filter'

const dataType = {
  name: 'template_type',
  meta: {
    icon: 'fa fa-fw fa fa-file-alt',
    label: trans('template_type', {}, 'data'),
    description: trans('template_type_desc', {}, 'data')
  },
  components: {
    details: TemplateTypeDisplay,
    input: TemplateTypeInput,
    search: TemplateTypeFilter,
    table: TemplateTypeCell
  }
}

export {
  dataType
}
