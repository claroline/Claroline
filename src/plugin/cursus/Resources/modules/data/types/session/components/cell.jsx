import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {UrlButton} from '#/main/app/buttons/url'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'
import {route as toolRoute} from '#/main/core/tool/routing'

import {route} from '#/plugin/cursus/routing'

const SessionCell = props => {
  if (!isEmpty(props.data)) {
    return (
      <UrlButton target={'#'+route(toolRoute('trainings')+'/catalog', props.data.course, props.data)}>
        {props.data.name}
      </UrlButton>
    )
  }

  return '-'
}

SessionCell.propTypes = DataCellTypes.propTypes

SessionCell.defaultProps = {
  data: {}
}

export {
  SessionCell
}
