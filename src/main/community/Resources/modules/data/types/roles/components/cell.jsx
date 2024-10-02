import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Badge} from '#/main/app/components/badge'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

const RolesCell = props => {
  if (isEmpty(props.data)) {
    return '-'
  }

  return (
    <div className="d-flex gap-1" role="presentation">
      {props.data.map(role => 
        <Badge key={role.name} subtle={true}>{trans(role.translationKey)}</Badge>
      )}
    </div>
  )
}

RolesCell.propTypes = DataCellTypes.propTypes

RolesCell.defaultProps = {
  data: []
}

export {
  RolesCell
}
