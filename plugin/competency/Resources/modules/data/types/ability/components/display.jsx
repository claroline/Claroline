import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {Ability as AbilityType} from '#/plugin/competency/administration/competency/prop-types'
import {AbilityCard} from '#/plugin/competency/administration/competency/data/components/ability-card'

const AbilityDisplay = (props) => props.data ?
  <AbilityCard
    data={props.data}
    size="sm"
    orientation="col"
  /> :
  <EmptyPlaceholder
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
