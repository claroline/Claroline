import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/data/components/card'

import {Quota as QuotaTypes} from '#/plugin/cursus/prop-types'

/**
 * Remove unless code
 */

const QuotaCard = props =>
  <DataCard
    {...props}
    className="quota"
    id={props.data.id}
    poster={null}
    icon="fa fa-graduation-cap"
    title={props.data.organization.name}
    subtitle={trans('threshold') + ' ' + props.data.threshold}
  />

QuotaCard.propTypes = {
  data: T.shape(
    QuotaTypes.propTypes
  ).isRequired
}

export {
  QuotaCard
}
