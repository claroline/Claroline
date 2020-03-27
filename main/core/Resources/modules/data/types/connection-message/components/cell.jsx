import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {UrlButton} from '#/main/app/buttons/url'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'
import {route} from '#/main/core/administration/routing'

const ConnectionMessageCell = props => {
  if (!isEmpty(props.data)) {
    return (
      <UrlButton target={'#'+route('main_settings')+'/messages/form/'+props.data.id}>
        {props.data.title}
      </UrlButton>
    )
  }

  return '-'
}

ConnectionMessageCell.propTypes = DataCellTypes.propTypes

ConnectionMessageCell.defaultProps = {
  data: {}
}

export {
  ConnectionMessageCell
}
