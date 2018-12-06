import {trans} from '#/main/app/intl/translation'

import {TemplateTypeCard} from '#/main/core/administration/template/data/components/template-type-card'

const TemplateTypeList = {
  definition: [
    {
      name: 'name',
      type: 'string',
      label: trans('name'),
      displayed: true,
      filterable: false,
      sortable: false,
      calculated: (rowData) => trans(rowData.name, {}, 'template'),
      primary: true
    }, {
      name: 'description',
      type: 'string',
      label: trans('description'),
      displayed: true,
      filterable: false,
      sortable: false,
      calculated: (rowData) => trans(`${rowData.name}_desc`, {}, 'template')
    }
  ],
  card: TemplateTypeCard
}

export {
  TemplateTypeList
}
