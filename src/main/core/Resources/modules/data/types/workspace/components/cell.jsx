import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {UrlButton} from '#/main/app/buttons/url'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

import {route} from '#/main/core/workspace/routing'

const WorkspaceCell = props => {
  if (!isEmpty(props.data)) {
    return (
      <UrlButton target={'#'+route(props.data)}>
        {props.data.name}
      </UrlButton>
    )
  }

  return '-'
}

WorkspaceCell.propTypes = DataCellTypes.propTypes

WorkspaceCell.defaultProps = {
  data: {}
}

export {
  WorkspaceCell
}
