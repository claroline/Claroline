import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'

import {GroupList} from '#/main/core/administration/user/group/components/group-list'
import {actions} from '#/main/core/administration/user/group/actions'

const GroupsList = props =>
  <ListData
    name="groups.list"
    fetch={{
      url: ['apiv2_group_list_managed'],
      autoload: true
    }}
    primaryAction={GroupList.open}
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
        dangerous: true
      }
    ]}
  />

GroupsList.propTypes = {
  updatePassword: T.func.isRequired
}

const Groups = connect(
  null,
  dispatch => ({
    updatePassword(groups) {
      dispatch(actions.updatePassword(groups.map(group => group.id)))
    }
  })
)(GroupsList)

export {
  Groups
}
