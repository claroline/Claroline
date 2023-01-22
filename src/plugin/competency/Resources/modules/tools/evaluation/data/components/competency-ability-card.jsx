import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/app/data/components/card'

import {CompetencyAbility as CompetencyAbilityType} from '#/plugin/competency/tools/evaluation/prop-types'

const CompetencyAbilityCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-atom"
    title={props.data.ability.name}
    subtitle={props.data.level.name}
  />

CompetencyAbilityCard.propTypes = {
  data: T.shape(CompetencyAbilityType.propTypes).isRequired
}

export {
  CompetencyAbilityCard
}
