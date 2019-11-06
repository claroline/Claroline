import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/administration/parameters/store/selectors'

const Icons = (props) =>
  <ListData
    name={selectors.STORE_NAME+'.icons.list'}
    fetch={{
      url: ['apiv2_icon_set_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/icons/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        scope: ['object'],
        target: `${props.path}/icons/form/${rows[0].id}`,
        displayed: rows[0].editable
      }
    ]}
    delete={{
      url: ['apiv2_icon_set_delete_bulk'],
      displayed: (rows) => !rows.find(iconSet => !iconSet.editable)
    }}
    definition={[
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'default',
        type: 'boolean',
        label: trans('default'),
        displayed: true
      }, {
        name: 'editable',
        type: 'boolean',
        label: trans('editable'),
        displayed: true
      }
    ]}
  />

export {
  Icons
}
