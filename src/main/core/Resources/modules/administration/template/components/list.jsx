import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'

import {TemplateTypeCard} from '#/main/core/data/types/template-type/components/card'
import {selectors} from '#/main/core/administration/template/store'

const TemplateList = (props) =>
  <ToolPage
    subtitle={trans(props.type)}
  >
    <ListData
      name={selectors.STORE_NAME + '.templates'}
      fetch={{
        url: ['apiv2_template_type_list', {type: props.type}],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `${props.path}/${props.type}/${row.id}`,
        label: trans('open', {}, 'actions')
      })}
      definition={[
        {
          name: 'name',
          type: 'translation',
          label: trans('name'),
          displayed: true,
          filterable: false,
          sortable: false,
          options: {
            domain: 'template'
          },
          primary: true
        }, {
          name: 'description',
          type: 'translation',
          label: trans('description'),
          displayed: true,
          filterable: false,
          sortable: false,
          calculated: (rowData) => `${rowData.name}_desc`,
          options: {
            domain: 'template'
          }
        }
      ]}
      card={TemplateTypeCard}
      selectable={false}
    />
  </ToolPage>

TemplateList.propTypes = {
  path: T.string.isRequired,
  type: T.oneOf(['email', 'pdf', 'sms'])
}

export {
  TemplateList
}
