import React, {useId} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'

const ActionCard = (props) => {
  const labelId = useId()
  const descriptionId = useId()

  return (
    <article
      className={classes('d-flex flex-row gap-3 p-3 align-items-start flex-wrap flex-lg-nowrap border rounded-3', props.className, {
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
        <p id={descriptionId} className="card-text text-body-secondary fs-sm">{props.help}</p>
      </div>

      <Button
        className={classes('btn flex-shrink-0 w-25', {
          'btn-danger' : props.dangerous,
          'btn-body': !props.dangerous
        })}
        {...props.action}
      />
    </article>
  )
}

ActionCard.propTypes = {
  className: T.string,
  title: T.string.isRequired,
  help: T.string.isRequired,
  action: T.object.isRequired,
  managerOnly: T.bool,
  dangerous: T.bool
}

ActionCard.defaultProps = {
  managerOnly: false,
  dangerous: false
}

export {
  ActionCard
}
