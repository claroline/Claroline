import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {UrlButton} from '#/main/app/buttons/url'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

import {route} from '#/main/core/administration/routing'

const OrganizationCell = props => {
  if (!isEmpty(props.data)) {
    return (
      <UrlButton target={'#'+route('community')+'/organizations/form/'+props.data.id}>
        {props.data.name}
      </UrlButton>
    )
  }

  return '-'
}

OrganizationCell.propTypes = DataCellTypes.propTypes

OrganizationCell.defaultProps = {
  data: {}
}

export {
  OrganizationCell
}
