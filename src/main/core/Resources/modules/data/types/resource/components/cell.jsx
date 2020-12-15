import React from 'react'

import {url} from '#/main/app/api'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

import {route as resourceRoute} from '#/main/core/resource/routing'

const ResourceCell = props => {
  if (props.data) {
    return (
      <Button
        type={URL_BUTTON}
        label={props.data.name}
        target={url(['claro_index']) + '#' + resourceRoute(props.data)}
      />
    )
  }

  return (
    <span className="text-muted">-</span>
  )
}

ResourceCell.propTypes = DataCellTypes.propTypes

export {
  ResourceCell
}
