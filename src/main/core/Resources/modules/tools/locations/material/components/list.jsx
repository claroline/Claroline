import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors} from '#/main/core/tools/locations/material/store'
import {MaterialCard} from '#/main/core/tools/locations/material/components/card'
import {MODAL_MATERIAL_PARAMETERS} from '#/main/core/tools/locations/material/modals/parameters'

const MaterialList = (props) =>
  <ToolPage
    subtitle={trans('materials', {}, 'location')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_material', {}, 'location'),
        modal: [MODAL_MATERIAL_PARAMETERS, {
          onSave: () => props.invalidateList()
        }],
        primary: true,
        displayed: props.editable
      }
    ]}
  >
    <ListData
      name={selectors.LIST_NAME}
      fetch={{
        url: ['apiv2_booking_material_list'],
        autoload: true
      }}
      delete={{
        url: ['apiv2_booking_material_delete_bulk']
      }}
      definition={[
        {
          name: 'code',
          type: 'string',
          label: trans('code')
        }, {
          name: 'name',
          label: trans('name'),
          displayed: true
        }, {
          name: 'description',
          type: 'html',
          label: trans('description'),
          displayed: true
        }, {
          name: 'quantity',
          type: 'number',
          label: trans('quantity'),
          displayed: true
        }
      ]}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        label: trans('open', {}, 'actions'),
        target: props.path+'/materials/'+row.id
      })}
      actions={(rows) => [
        {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_MATERIAL_PARAMETERS, {
            room: rows[0],
            onSave: () => props.invalidateList()
          }],
          displayed: props.editable,
          group: trans('management'),
          scope: ['object']
        }
      ]}
      card={MaterialCard}
    />
  </ToolPage>

MaterialList.propTypes = {
  path: T.string.isRequired,
  invalidateList: T.func.isRequired,
  editable: T.bool.isRequired
}

export {
  MaterialList
}
