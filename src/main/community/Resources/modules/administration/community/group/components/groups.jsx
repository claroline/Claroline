import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as baseSelectors} from '#/main/community/administration/community/store'
import {GroupList} from '#/main/community/administration/community/group/components/group-list'
import {actions} from '#/main/community/administration/community/group/store'

const GroupsList = props =>
  <ListData
    name={`${baseSelectors.STORE_NAME}.groups.list`}
    fetch={{
      url: ['apiv2_group_list_managed'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/groups/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    delete={{
      url: ['apiv2_group_delete_bulk']
    }}
    definition={GroupList.definition}
    card={GroupList.card}
    actions={(rows) => [
      {
        type: 'callback',
        icon: 'fa fa-fw fa-lock',
        label: trans('change_password'),
        scope: ['object'],
        confirm: {
          title: trans('group_password_reset'),
          message: trans('send_password_reset', {
            groups: rows.map(group => group.name).join(', ')
          })
        },
        callback: () => props.updatePassword(rows),
        group: trans('management')
      }
    ]}
  />

GroupsList.propTypes = {
  path: T.string.isRequired,
  updatePassword: T.func.isRequired
}

const Groups = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    updatePassword(groups) {
      dispatch(actions.updatePassword(groups.map(group => group.id)))
    }
  })
)(GroupsList)

export {
  Groups
}
