import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool'

import {selectors} from '#/main/core/tools/locations/store'
import {LocationCard} from '#/main/core/data/types/location/components/card'

const LocationList = props =>
  <ToolPage
    /*root={true}*/
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_location', {}, 'location'),
        target: `${props.path}/new`,
        primary: true
      }
    ]}
  >
    <ListData
      name={`${selectors.STORE_NAME}.list`}
      fetch={{
        url: ['apiv2_location_list'],
        autoload: true
      }}
      definition={[
        {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true,
          primary: true
        }, {
          name: 'address',
          type: 'address',
          label: trans('address'),
          displayed: true
        }, {
          name: 'phone',
          type: 'string',
          label: trans('phone'),
          displayed: true
        }
      ]}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `${props.path}/${row.id}`,
        label: trans('open', {}, 'actions')
      })}
      delete={{
        url: ['apiv2_location_delete']
      }}
      actions={(rows) => [
        {
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          target: `${props.path}/${rows[0].id}/edit`,
          group: trans('management'),
          scope: ['object']
        }
      ]}
      card={LocationCard}
    />
  </ToolPage>

LocationList.propTypes = {
  path: T.string.isRequired
}

export {
  LocationList
}
