import React from 'react'

import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

import {UserMicro} from '#/main/core/user/components/micro'

const UserCell = props =>
  <UserMicro {...props.data} link={true} />

UserCell.propTypes = DataCellTypes.propTypes

UserCell.defaultProps = {
  data: {}
}

export {
  UserCell
}
