import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/theme/administration/appearance/icon/store/selectors'

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
    delete={{
      url: ['apiv2_icon_set_delete_bulk'],
      displayed: (rows) => !rows.find(iconSet => !iconSet.restrictions.locked)
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
        name: 'restrictions.locked',
        alias: 'locked',
        type: 'boolean',
        label: trans('locked'),
        displayed: true
      }
    ]}
  />

export {
  Icons
}
