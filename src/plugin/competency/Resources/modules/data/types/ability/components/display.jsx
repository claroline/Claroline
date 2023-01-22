import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Ability as AbilityType} from '#/plugin/competency/tools/evaluation/prop-types'
import {AbilityCard} from '#/plugin/competency/tools/evaluation/data/components/ability-card'

const AbilityDisplay = (props) => props.data ?
  <AbilityCard
    data={props.data}
    size="sm"
    orientation="col"
  /> :
  <ContentPlaceholder
    size="lg"
    icon="fa fa-arrow-up"
    title={trans('ability.none', {}, 'competency')}
  />

AbilityDisplay.propTypes = {
  data: T.shape(AbilityType.propTypes)
}

export {
  AbilityDisplay
}
