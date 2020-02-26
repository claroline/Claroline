import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {DataCard} from '#/main/app/data/components/card'
import {UserMicro} from '#/main/core/user/components/micro'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'

import {Paper as PaperTypes} from '#/plugin/exo/resources/quiz/papers/prop-types'

const PaperCard = props => {
  let size = 80
  if ('sm' == props.size) {
    size = 60
  } else if ('lg' === props.size) {
    size = 100
  }

  return (
    <DataCard
      {...props}
      className={props.className}
      id={props.data.id}
      icon={props.data.total ?
        <ScoreGauge
          type="user"
          value={props.data.score}
          total={props.data.total}
          width={size}
          height={size}
          displayValue={value => undefined === value || null === value ? '?' : value+''}
        /> :
        <UserAvatar picture={get(props.data, 'user.picture')} alt={true} />
      }
      title={props.data.user ? props.data.user.firstName + ' ' + props.data.user.lastName : trans('unknown')}
      subtitle={trans('attempt', {number: props.data.number}, 'quiz')}
      flags={[
        props.data.finished && ['fa fa-fw fa-check', trans('finished', {}, 'quiz')],
        !props.data.finished && ['fa fa-fw fa-sync', trans('in_progress', {}, 'quiz')]
      ].filter(flag => !!flag)}
      footer={
        <span
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}
        >
          {props.data.total &&
            <UserMicro {...props.data.user} />
          }

          {props.data.endDate &&
            trans('finished_at', {date: displayDate(props.data.endDate, false, true)})
          }

          {!props.data.endDate &&
            trans('started_at', {date: displayDate(props.data.startDate, false, true)})
          }
        </span>
      }
    />
  )
}

PaperCard.propTypes = {
  size: T.oneOf(['sm', 'lg']),
  className: T.string,
  data: T.shape(
    PaperTypes.propTypes
  ).isRequired
}

export {
  PaperCard
}
