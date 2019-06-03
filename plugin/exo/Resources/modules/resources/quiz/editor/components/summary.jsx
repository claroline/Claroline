import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {FormStatus} from '#/main/app/content/form/components/status'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LinkButton} from '#/main/app/buttons/link/components/button'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

const StepLink = props =>
  <li className="quiz-navlink">
    {props.errors &&
      <FormStatus
        id={props.id}
        validating={props.validating}
        position="right"
      />
    }

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
      {props.title ? props.title.substr(0, 30) : trans('step', {number: props.number}, 'quiz')}
    </LinkButton>
  </li>

StepLink.propTypes = {
  id: T.string.isRequired,
  number: T.number.isRequired,
  title: T.string,
  errors: T.bool.isRequired,
  validating: T.bool.isRequired,
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
      <ul className="quiz-navbar scroller">
        <li className="quiz-navlink">
          <LinkButton target="/edit/parameters">
            {!isEmpty(omit(this.props.errors, 'steps')) &&
              <FormStatus
                id="quiz-parameters-errors"
                validating={this.props.validating}
                position="right"
              />
            }

            <span className="fa fa-cog" />
            <span className="hidden-xs">{trans('parameters')}</span>
          </LinkButton>
        </li>

        {this.props.steps.map((step, index) =>
          <StepLink
            key={step.id}
            id={step.id}
            number={index + 1}
            title={step.title}
            actions={step.actions}
            validating={this.props.validating}
            errors={!isEmpty(get(this.props.errors, `steps[${index}]`))}
          />
        )}

        <li className="quiz-navlink">
          <CallbackButton callback={() => this.props.add()}>
            <span className="fa fa-plus" />
            <span className="hidden-xs">{trans('add_step', {}, 'quiz')}</span>
          </CallbackButton>
        </li>
      </ul>
    )
  }
}

EditorSummary.propTypes = {
  active: T.string,
  errors: T.object,
  validating: T.bool.isRequired,
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
  errors: {},
  steps: []
}

export {
  EditorSummary
}
