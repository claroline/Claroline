import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors} from '#/main/core/tools/locations/room/store'
import {RoomCard} from '#/main/core/data/types/room/components/card'
import {MODAL_ROOM_PARAMETERS} from '#/main/core/tools/locations/room/modals/parameters'

const RoomList = (props) =>
  <ToolPage
    subtitle={trans('rooms', {}, 'location')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_room', {}, 'location'),
        modal: [MODAL_ROOM_PARAMETERS, {
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
        url: ['apiv2_location_room_list'],
        autoload: true
      }}
      delete={{
        url: ['apiv2_location_room_delete_bulk']
      }}
      definition={[
        {
          name: 'code',
          type: 'string',
          label: trans('code')
        }, {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true
        }, {
          name: 'description',
          type: 'html',
          label: trans('description'),
          displayed: true
        }, {
          name: 'capacity',
          type: 'number',
          label: trans('capacity'),
          displayed: true
        }
      ]}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        label: trans('open', {}, 'actions'),
        target: props.path+'/rooms/'+row.id
      })}
      actions={(rows) => [
        {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_ROOM_PARAMETERS, {
            room: rows[0],
            onSave: () => props.invalidateList()
          }],
          displayed: props.editable,
          group: trans('management'),
          scope: ['object']
        }
      ]}
      card={RoomCard}
    />
  </ToolPage>

RoomList.propTypes = {
  path: T.string.isRequired,
  invalidateList: T.func.isRequired,
  editable: T.bool.isRequired
}

export {
  RoomList
}
