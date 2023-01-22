import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Ability as AbilityType} from '#/plugin/competency/tools/evaluation/prop-types'

const AbilityCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-atom"
    title={props.data.name}
    subtitle={trans('ability.min_resource_count', {}, 'competency') + ' : ' + props.data.minResourceCount}
  />

AbilityCard.propTypes = {
  data: T.shape(AbilityType.propTypes).isRequired
}

export {
  AbilityCard
}
