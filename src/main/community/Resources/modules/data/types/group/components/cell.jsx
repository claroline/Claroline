import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'
import {UrlButton} from '#/main/app/buttons'

import {route} from '#/main/community/group/routing'

const GroupCell = props => {
  if (!props.placeholder && isEmpty(props.data)) {
    return '-'
  }

  return (
    <UrlButton target={'#'+route(props.data)}>
      {props.data.name}
    </UrlButton>
  )
}

GroupCell.propTypes = DataCellTypes.propTypes

GroupCell.defaultProps = {
  data: {},
  placeholder: true
}

export {
  GroupCell
}
