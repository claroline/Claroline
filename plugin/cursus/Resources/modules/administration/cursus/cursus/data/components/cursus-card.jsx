import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/data/components/card'

const CursusCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={props.data.meta.course ? 'fa fa-tasks' : 'fa fa-database'}
    title={props.data.title}
    subtitle={props.data.code}
    poster={props.data.meta.icon ? asset(props.data.meta.icon) : null}
    contentText={props.data.description}
    flags={[
      props.data.meta.course && ['fa fa-tasks', trans('course', {}, 'cursus')],
      props.data.meta.blocking && ['fa fa-lock', trans('blocking', {}, 'cursus')]
    ].filter(flag => !!flag)}
  />

CursusCard.propTypes = {
  data: T.shape({
    id: T.string.isRequired,
    title: T.string,
    code: T.string,
    description: T.string,
    meta: T.shape({
      icon: T.string,
      course: T.object,
      blocking: T.bool
    })
  }).isRequired
}

export {
  CursusCard
}
