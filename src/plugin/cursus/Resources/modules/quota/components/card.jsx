import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Quota as QuotaTypes} from '#/plugin/cursus/prop-types'

const QuotaCard = props =>
  <DataCard
    {...props}
    className="quota"
    id={props.data.id}
    poster={null}
    icon="fa fa-graduation-cap"
    title={props.data.organization.name}
    subtitle={trans('threshold', {}, 'cursus') + ' ' + props.data.threshold}
  />

QuotaCard.propTypes = {
  data: T.shape(
    QuotaTypes.propTypes
  ).isRequired
}

export {
  QuotaCard
}
