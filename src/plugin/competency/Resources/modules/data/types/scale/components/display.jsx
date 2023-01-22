import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Scale as ScaleType} from '#/plugin/competency/tools/evaluation/prop-types'
import {ScaleCard} from '#/plugin/competency/tools/evaluation/data/components/scale-card'

const ScaleDisplay = (props) => props.data ?
  <ScaleCard
    data={props.data}
    size="sm"
    orientation="col"
  /> :
  <ContentPlaceholder
    size="lg"
    icon="fa fa-arrow-up"
    title={trans('scale.none', {}, 'competency')}
  />

ScaleDisplay.propTypes = {
  data: T.shape(ScaleType.propTypes)
}

export {
  ScaleDisplay
}
