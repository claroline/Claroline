import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

import {UserMicroList} from '#/main/core/user/components/micro-list'

const UsersCell = props => {
  if (isEmpty(props.data)) {
    return '-'
  }

  return (
    <UserMicroList
      id={props.id}
      label={props.label}
      users={props.data}
      link={true}
    />
  )
}

UsersCell.propTypes = DataCellTypes.propTypes

UsersCell.defaultProps = {
  data: []
}

export {
  UsersCell
}
