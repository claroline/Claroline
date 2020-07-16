import React from 'react'
import {PropTypes as T} from 'prop-types'
import {DataCard} from '#/main/app/data/components/card'

const CursusGroupCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-users"
    title={props.data.group.name}
    subtitle={props.data.registrationDate}
  />

CursusGroupCard.propTypes = {
  data: T.shape({
    id: T.string,
    group: T.shape({
      name: T.string
    }),
    registrationDate: T.string
  }).isRequired
}

export {
  CursusGroupCard
}
