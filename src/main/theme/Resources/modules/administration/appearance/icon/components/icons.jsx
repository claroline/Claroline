import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/theme/administration/appearance/icon/store/selectors'

/*{
  name: 'add',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-plus',
  label: trans('add_icon_set'),
  target: props.path+'/icons/form',
  primary: true
}*/

const Icons = (props) =>
  <ListData
    name={selectors.STORE_NAME+'.list'}
    fetch={{
      url: ['apiv2_icon_set_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/appearance/icons/form/${row.id}`,
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
