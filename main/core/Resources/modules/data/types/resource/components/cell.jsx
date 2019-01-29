import React from 'react'

import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'

import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

const ResourceCell = props => {
  if (props.data) {
    return (
      <Button
        type={URL_BUTTON}
        label={props.data.name}
        target={['claro_resource_show_short', {
          id: props.data.id
        }]}
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
