import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Organization as OrganizationTypes} from '#/main/community/prop-types'
import {OrganizationCard} from '#/main/core/user/data/components/organization-card'

const OrganizationsDisplay = (props) => {
  if (!isEmpty(props.data)) {
    return (
      <Fragment>
        {props.data.map(organization =>
          <OrganizationCard
            key={`organization-card-${organization.id}`}
            data={organization}
            size="xs"
          />
        )}
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-building"
      title={trans('no_organization')}
    />
  )
}

OrganizationsDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    OrganizationTypes.propTypes
  ))
}

export {
  OrganizationsDisplay
}
