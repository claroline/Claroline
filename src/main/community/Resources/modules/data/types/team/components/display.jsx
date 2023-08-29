import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Team as TeamTypes} from '#/main/community/prop-types'
import {TeamCard} from '#/main/community/team/components/card'

const TeamDisplay = (props) => {
  if (!isEmpty(props.data)) {
    return (
      <Fragment>
        {props.data.map(team =>
          <TeamCard
            key={`team-card-${team.id}`}
            data={team}
            size="xs"
          />
        )}
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-fw fa-user-group"
      title={trans('no_team')}
    />
  )
}

TeamDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    TeamTypes.propTypes
  ))
}

export {
  TeamDisplay
}
