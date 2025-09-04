import React, {useId} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {TextSkeleton} from '#/main/app/components/placeholder'

const ActionCardSkeleton = ({dangerous = false}) =>
  <article className={classes('d-flex flex-row gap-3 p-3 align-items-start flex-wrap flex-lg-nowrap border rounded-2', {
      'border-danger': dangerous
    })}>
    <div className="flex-fill placeholder-glow" role="presentation">
      <b className="placeholder rounded-1 d-flex align-items-baseline mb-2 gap-2 w-50">&nbsp;</b>
      <TextSkeleton className="card-text text-body-secondary fs-sm" rows={3} />
    </div>

    <span className={classes('placeholder btn disabled flex-shrink-0 w-25', {
      'btn-danger' : dangerous,
      'btn-body': !dangerous
    })} />
  </article>

const ActionCard = (props) => {
  const labelId = useId()
  const descriptionId = useId()

  return (
    <article
      className={classes('d-flex flex-row gap-3 p-3 align-items-start flex-wrap flex-lg-nowrap border rounded-2', props.className, {
        'border-danger': props.dangerous
      })}
      aria-labelledby={labelId}
      aria-describedby={descriptionId}
    >
      <div className="flex-fill" role="presentation">
        <b id={labelId} className="d-flex align-items-baseline mb-2 gap-2">
          {props.title}
          {props.managerOnly &&
            <span className={classes('badge', props.dangerous ? 'text-danger-emphasis bg-danger-subtle' : 'text-secondary-emphasis bg-secondary-subtle')}>
              {trans('confidentiality_manager')}
            </span>
          }
        </b>
        <p id={descriptionId} className="card-text text-body-secondary fs-sm">{props.description}</p>
      </div>

      <Button
        className={classes('btn flex-shrink-0 w-25', {
          'btn-danger' : props.dangerous,
          'btn-body': !props.dangerous
        })}
        dangerous={props.dangerous}
        label={props.labelShort || props.label}
        {...omit(props, 'className', 'icon', 'title', 'labelShort', 'label', 'description', 'managerOnly')}
      />
    </article>
  )
}

ActionCard.propTypes = {
  className: T.string,
  name: T.string.isRequired,
  title: T.string.isRequired,
  label: T.string.isRequired,
  labelShort: T.string,
  description: T.string,
  managerOnly: T.bool,
  dangerous: T.bool
}

ActionCard.defaultProps = {
  managerOnly: false,
  dangerous: false
}

export {
  ActionCard,
  ActionCardSkeleton
}
