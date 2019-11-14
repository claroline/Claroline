import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isUndefined from 'lodash/isUndefined'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {CallbackButton} from '#/main/app/buttons/callback'
import {ContentTitle} from '#/main/app/content/components/title'

/**
 * Renders the form navigation.
 *
 * @param props
 * @constructor
 */
const FormStepperNav = props =>
  <nav className="form-stepper-nav">
    {props.steps.map((step, stepIndex) => {
      const done = -1 !== props.done.indexOf(stepIndex)
      if (done && props.activeIndex !== stepIndex) {
        return (
          <CallbackButton
            key={stepIndex}
            className="form-stepper-link done"
            callback={() => props.navigate(stepIndex)}
          >
            <span className="form-step-badge">
              {step.icon &&
                <span className={step.icon} />
              }

              {!step.icon && (stepIndex+1)}
            </span>

            {step.title}
          </CallbackButton>
        )
      }

      return (
        <span
          key={stepIndex}
          className={classes('form-stepper-link', {
            active: props.activeIndex === stepIndex,
            done: done
          })}
        >
          <span className="form-step-badge">
            {step.icon &&
              <span className={step.icon} />
            }

            {!step.icon && (stepIndex+1)}
          </span>

          {step.title}
        </span>
      )
    })}
  </nav>

FormStepperNav.propTypes = {
  activeIndex: T.number.isRequired,
  steps: T.arrayOf(T.shape({
    icon: T.string,
    title: T.string.isRequired,
    component: T.any,
    render: T.func
  })).isRequired,
  done: T.arrayOf(T.number),
  navigate: T.func.isRequired
}

/**
 * Renders the form footer (aka. next and submit buttons).
 *
 * @param props
 * @constructor
 */
const FormStepperFooter = props =>
  <div className="form-stepper-footer">
    {!isUndefined(props.previousStep) &&
      <Button
        className="btn btn-link btn-emphasis"
        type={CALLBACK_BUTTON}
        label={trans('previous')}
        callback={() => props.navigate(props.previousStep)}
      />
    }

    {!isUndefined(props.nextStep) &&
      <Button
        className="btn btn-emphasis btn-next"
        type={CALLBACK_BUTTON}
        label={trans('next')}
        callback={() => props.navigate(props.nextStep)}
        primary={true}
      />
    }

    {isUndefined(props.nextStep) &&
      <Button
        className="btn btn-emphasis btn-next"
        {...props.submit}
        primary={true}
      />
    }
  </div>

FormStepperFooter.propTypes = {
  previousStep: T.number,
  nextStep: T.number,
  submit: T.shape(
    ActionTypes.propTypes
  ).isRequired,
  navigate: T.func.isRequired
}

class FormStepper extends Component {
  constructor(props) {
    super(props)

    this.state = {
      activeStep: 0,
      doneSteps: []
    }

    this.navigate = this.navigate.bind(this)
  }

  navigate(stepIndex) {
    if (stepIndex > this.state.activeStep) {
      // go forward
      let valid = true
      if (this.props.steps[this.state.activeStep].validate) {
        // validate the step
        valid = this.props.steps[this.state.activeStep].validate()
      }

      if (valid) {
        const doneSteps = this.state.doneSteps.slice()
        if (-1 === doneSteps.indexOf(this.state.activeStep)) {
          doneSteps.push(this.state.activeStep)
        }

        this.setState({
          doneSteps: doneSteps,
          activeStep: stepIndex
        })
      } else {
        // not valid
        const doneSteps = this.state.doneSteps.slice()
        const pos = doneSteps.indexOf(this.state.activeStep)
        if (-1 !== pos) {
          doneSteps.splice(pos, 1)
        }

        this.setState({
          doneSteps: doneSteps
        })
      }
    } else {
      // go backward
      this.setState({activeStep: stepIndex})
    }
  }

  render() {
    return (
      <div className={classes('form-stepper', this.props.className)}>
        <FormStepperNav
          steps={this.props.steps}
          done={this.state.doneSteps}
          activeIndex={this.state.activeStep}
          navigate={this.navigate}
        />

        <ContentTitle
          level={this.props.level}
          displayLevel={this.props.displayLevel}
          title={this.props.steps[this.state.activeStep].title}
        />

        {this.props.steps[this.state.activeStep].component ?
          createElement(this.props.steps[this.state.activeStep].component) :
          this.props.steps[this.state.activeStep].render()
        }

        <FormStepperFooter
          previousStep={this.props.steps[this.state.activeStep-1] ? this.state.activeStep-1 : undefined}
          nextStep={this.props.steps[this.state.activeStep+1] ? this.state.activeStep+1 : undefined}
          navigate={this.navigate}
          submit={this.props.submit}
        />
      </div>
    )
  }
}

FormStepper.propTypes = {
  className: T.string,
  level: T.number,
  displayLevel: T.number,
  steps: T.arrayOf(T.shape({
    icon: T.string,
    title: T.string.isRequired,
    component: T.any,
    render: T.func,
    validate: T.func
  })).isRequired,
  submit: T.shape(
    ActionTypes.propTypes
  ).isRequired
}

FormStepper.defaultProps = {
  level: 2
}

export {
  FormStepper
}
