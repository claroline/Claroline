import React from 'react'
import {trans} from '#/main/core/translation'
import {PropTypes as T} from 'prop-types'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {constants as listConst} from '#/main/core/data/list/constants'
import {UserActionCard} from '#/main/core/layout/logs/components/user-action-card.jsx'

const UserLogList = props =>
  <DataListContainer
    name="userActions"
    fetch={{
      url: props.listUrl,
      autoload: true
    }}
    open={false}
    delete={false}
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
  />

UserLogList.propTypes = {
  listUrl: T.oneOfType([T.string, T.array]).isRequired
}

export {
  UserLogList
}