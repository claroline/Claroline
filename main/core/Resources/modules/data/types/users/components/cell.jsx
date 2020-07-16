import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {MenuButton} from '#/main/app/buttons/menu'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

import {UserMicro} from '#/main/core/user/components/micro'

const UsersCell = props => {
  if (isEmpty(props.data)) {
    return '-'
  }

  if (1 === props.data.length) {
    return (
      <UserMicro {...props.data[0]} link={true} />
    )
  }

  return (
    <div>
      <UserMicro {...props.data[0]} link={true} />

      <MenuButton
        id={props.id+'-list'}
        className="badge icon-with-text-left"
        menu={
          <ul className="dropdown-menu dropdown-menu-right">
            <li role="heading" className="dropdown-header">{props.label}</li>
            {props.data.map(user =>
              <li role="presentation" key={user.id}>
                <UserMicro {...user} link={true} />
              </li>
            )}
          </ul>
        }
      >
        + {props.data.length - 1}
      </MenuButton>
    </div>
  )
}

UsersCell.propTypes = DataCellTypes.propTypes

UsersCell.defaultProps = {
  data: {}
}

export {
  UsersCell
}
