import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Validation as ValidationTypes} from '#/plugin/cursus/prop-types'

/**
 * Remove unless code
 */

const ValidationCard = props =>
  <DataCard
    {...props}
    className="validation"
    id={props.data.id}
    poster={null}
    icon="fa fa-graduation-cap"
    title={props.data.organization.name}
    subtitle={trans('threshold') + ' ' + props.data.threshold}
  />

ValidationCard.propTypes = {
  data: T.shape(
    ValidationTypes.propTypes
  ).isRequired
}

export {
  ValidationCard
}
