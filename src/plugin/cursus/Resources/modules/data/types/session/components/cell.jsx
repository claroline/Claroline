import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {UrlButton} from '#/main/app/buttons/url'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

import {route} from '#/plugin/cursus/routing'

const SessionCell = props => {
  if (!isEmpty(props.data)) {
    return (
      <UrlButton target={'#'+route(props.course, props.data)}>
        {props.data.name}
      </UrlButton>
    )
  }

  return '-'
}

implementPropTypes(SessionCell, DataCellTypes, {
  course: T.object.isRequired
})

SessionCell.defaultProps = {
  data: {}
}

export {
  SessionCell
}
