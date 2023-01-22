import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Competency as CompetencyTypes} from '#/plugin/competency/tools/evaluation/prop-types'

const CompetencyCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-atom"
    title={props.data.name}
    subtitle={props.data.scale.name}
    contentText={props.data.description}
  />

CompetencyCard.propTypes = {
  data: T.shape(
    CompetencyTypes.propTypes
  ).isRequired
}

const CompetencyTreeCard = props =>
  <div className="competency-card">
    <DataCard
      {...props}
      id={props.data.id}
      icon="fa fa-atom"
      title={props.data.name}
      subtitle={props.data.scale.name}
      flags={[
        props.data.abilities && 0 < props.data.abilities.length && ['fa fa-atom', trans('ability.contains_desc', {}, 'competency')]
      ].filter(flag => !!flag)}
      contentText={props.data.description}
    />

    {props.data.abilities && 0 < props.data.abilities.length &&
      <ul className="competency-abilities">
        {props.data.abilities.map(competencyAbility =>
          <li className="competency-ability" key={competencyAbility.id}>
            <span className="competency-ability-name">
              {competencyAbility.ability.name}
            </span>

            {competencyAbility.level.name}
          </li>
        )}
      </ul>
    }
  </div>

CompetencyTreeCard.propTypes = {
  data: T.shape(
    CompetencyTypes.propTypes
  ).isRequired
}

export {
  CompetencyCard,
  CompetencyTreeCard
}
