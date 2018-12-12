import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/app/content/card/components/data'

import {Competency as CompetencyType} from '#/plugin/competency/administration/competency/prop-types'

const CompetencyCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-graduation-cap"
    title={props.data.name}
    subtitle={props.data.scale.name}
    contentText={props.data.description}
  />

CompetencyCard.propTypes = {
  data: T.shape(CompetencyType.propTypes).isRequired
}

export {
  CompetencyCard
}
