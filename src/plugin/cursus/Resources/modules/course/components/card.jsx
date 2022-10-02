import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/data/components/card'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'

const CourseCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, {
      'data-card-muted': get(props.data, 'restrictions.hidden', false) || !get(props.data, 'restrictions.active', false)
    })}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    icon="fa fa-graduation-cap"
    title={props.data.name}
    subtitle={props.data.code}
    contentText={props.data.plainDescription || props.data.description}
    flags={[
      get(props.data, 'restrictions.hidden')           && ['fa fa-eye-slash', trans('training_hidden', {}, 'cursus')],
      get(props.data, 'registration.selfRegistration') && ['fa fa-globe',     trans('training_public_registration', {}, 'cursus')]
    ].filter(flag => !!flag)}
    footer={!isEmpty(props.data.tags) &&
      <div className="tags">
        {props.data.tags.map(tag =>
          <span key={tag} className="tag label label-info">
            <span className="fa fa-fw fa-tag icon-with-text-right" />
            {tag}
          </span>
        )}
      </div>
    }
  />

CourseCard.propTypes = {
  className: T.string,
  data: T.shape(
    CourseTypes.propTypes
  ).isRequired
}

export {
  CourseCard
}
