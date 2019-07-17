import React from 'react'
import {trans} from '#/main/app/intl/translation'
import {PropTypes as T} from 'prop-types'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {UserActionCard} from '#/main/core/layout/logs/components/user-action-card'

const UserLogList = props =>
  <ListData
    name={props.name}
    fetch={{
      url: props.listUrl,
      autoload: true
    }}
    definition={[
      {
        name: 'doer.name',
        type: 'string',
        label: trans('user'),
        displayed: true,
        primary: true
      }, {
        name: 'actions',
        type: 'number',
        label: trans('actions'),
        displayed: true
      }
    ]}
    
    card={UserActionCard}
    
    display={{
      available : [listConst.DISPLAY_TABLE, listConst.DISPLAY_TABLE_SM, listConst.DISPLAY_LIST],
      current: listConst.DISPLAY_LIST
    }}
    selectable={false}
  />

UserLogList.propTypes = {
  listUrl: T.oneOfType([T.string, T.array]).isRequired
}

UserLogList.defaultProps = {
  name: 'userActions'
}

export {
  UserLogList
}