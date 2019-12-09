import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

import {UserMicro} from '#/main/core/user/components/micro'

const UserCell = props => {
  if (!props.placeholder && isEmpty(props.data)) {
    return '-'
  }

  return (
    <UserMicro {...props.data} link={true} />
  )
}

UserCell.propTypes = DataCellTypes.propTypes

UserCell.defaultProps = {
  data: {},
  placeholder: true
}

export {
  UserCell
}
