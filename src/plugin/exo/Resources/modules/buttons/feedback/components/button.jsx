import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {isHtmlEmpty} from '#/main/app/data/types/html/validators'
import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {POPOVER_BUTTON} from '#/main/app/buttons/popover'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

/**
 * Feedback button.
 * Renders a component that will open the feedback editor.
 */
const FeedbackEditorButton = (props) =>
  <Button
    {...omit(props, 'feedback', 'toggle')}
    id={`feedback-${props.id}`}
    className={classes('btn btn-text-secondary', props.className)}
    type={CALLBACK_BUTTON}
    icon={classes('fa fa-fw fa-comments', {
      'fa-regular': isHtmlEmpty(props.feedback)
    })}
    label={props.label}
    callback={props.toggle}
    tooltip="top"
  />

FeedbackEditorButton.propTypes = {
  id: T.oneOfType([T.string, T.number]).isRequired,
  label: T.string.isRequired,
  className: T.string,
  feedback: T.string,
  toggle: T.func.isRequired
}

/**
 * Feedback button.
 * Renders a component that will open an answer feedback.
 */
const FeedbackButton = props => {
  if (!props.feedback || isHtmlEmpty(props.feedback)) {
    return (
      <span className="btn-link btn-feedback" />
    )
  }

  return (
    <Button
      {...omit(props, 'feedback')}
      id={`feedback-${props.id}`}
      className={classes('btn-link btn-feedback', props.className)}
      type={POPOVER_BUTTON}
      icon="fa fa-fw fa-comments"
      label={trans('show-feedback', {}, 'actions')}
      tooltip="top"
      popover={{
        className: 'feedback-popover',
        position: 'bottom',
        content: <ContentHtml>{props.feedback}</ContentHtml>
      }}
    />
  )
}

FeedbackButton.propTypes = {
  id: T.oneOfType([T.string, T.number]).isRequired,
  className: T.string,
  feedback: T.string
}

export {
  FeedbackButton,
  FeedbackEditorButton
}
