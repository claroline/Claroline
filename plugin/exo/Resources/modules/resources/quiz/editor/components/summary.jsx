import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LinkButton} from '#/main/app/buttons/link/components/button'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

const StepLink = props =>
  <div className="quiz-navlink">
    {props.actions &&
      <Toolbar
        id={props.id}
        className="step-toolbar"
        buttonName="btn-link"
        tooltip="bottom"
        toolbar="more"
        actions={props.actions}
      />
    }

    <LinkButton
      target={`/edit/${props.id}`}
    >
      {props.title ? props.title : trans('step', {number: props.number}, 'quiz')}
    </LinkButton>
  </div>

StepLink.propTypes = {
  id: T.string.isRequired,
  number: T.number.isRequired,
  title: T.string,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  ))
}

class EditorSummary extends Component {
  componentDidMount() {
    this.scrollToActive()
  }

  componentDidUpdate(prevProps) {
    if (prevProps.active !== this.props.active) {
      this.scrollToActive()
    }
  }

  scrollToActive() {
    scrollTo(`step-link-${this.props.active}`)
  }

  render() {
    return (
      <div className="quiz-navbar scroller">
        <LinkButton
          className="quiz-navlink"
          target="/edit/parameters"
        >
          <span className="fa fa-cog" />
          <span className="hidden-xs">{trans('parameters')}</span>
        </LinkButton>

        {this.props.steps.map((step, index) =>
          <StepLink
            key={step.id}
            id={step.id}
            number={index + 1}
            title={step.title}
            actions={step.actions}
          />
        )}

        <CallbackButton
          className="quiz-navlink"
          callback={() => this.props.add()}
        >
          <span className="fa fa-plus" />
          <span className="hidden-xs">{trans('add_step', {}, 'quiz')}</span>
        </CallbackButton>
      </div>
    )
  }
}

EditorSummary.propTypes = {
  active: T.string,
  steps: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string,
    actions: T.arrayOf(T.shape(
      ActionTypes.propTypes
    ))
  })),
  add: T.func.isRequired
}

EditorSummary.defaultProps = {
  steps: []
}

export {
  EditorSummary
}
