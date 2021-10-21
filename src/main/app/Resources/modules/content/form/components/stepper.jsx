import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isUndefined from 'lodash/isUndefined'

import {scrollTo} from '#/main/app/dom/scroll'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {CountGauge} from '#/main/core/layout/gauge/components/count-gauge'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

/**
 * Renders the form footer (aka. next and submit buttons).
 */
const FormStepperFooter = props =>
  <div className="form-stepper-footer">
    {!isUndefined(props.previousStep) &&
      <Button
        className="btn btn-link btn-emphasis"
        type={CALLBACK_BUTTON}
        label={trans('previous')}
        callback={() => props.navigate(props.previousStep)}
        onClick={() => scrollTo('.form-stepper')}
      />
    }

    {!isUndefined(props.nextStep) &&
      <Button
        className="btn btn-emphasis btn-next"
        type={CALLBACK_BUTTON}
        label={trans('next')}
        callback={() => props.navigate(props.nextStep)}
        primary={true}
        onClick={() => scrollTo('.form-stepper')}
      />
    }

    {isUndefined(props.nextStep) &&
      <Button
        className="btn btn-emphasis btn-next"
        {...props.submit}
        primary={true}
        onClick={() => scrollTo('.form-stepper')}
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
      <div className={classes('form-stepper component-container', this.props.className)}>
        <ProgressBar
          className="progress-minimal"
          value={Math.floor(((this.state.activeStep + 1) / (this.props.steps.length)) * 100)}
          size="xs"
          type="user"
        />

        <ContentTitle
          level={this.props.level}
          displayLevel={this.props.displayLevel}
          title={this.props.steps[this.state.activeStep].title}
          subtitle={this.props.steps[this.state.activeStep+1] ? trans('next_step', {step: this.props.steps[this.state.activeStep+1].title}) : undefined}
        >
          <CountGauge
            className="h-gauge"
            value={this.state.activeStep + 1}
            total={this.props.steps.length}
            type="user"
            displayValue={(value) => value + ' / ' + this.props.steps.length}
            width={70}
            height={70}
          />
        </ContentTitle>

        {this.props.steps[this.state.activeStep].component ?
          createElement(this.props.steps[this.state.activeStep].component) :
          this.props.steps[this.state.activeStep].render()
        }

        {this.props.children}

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
  ).isRequired,
  children: T.any
}

FormStepper.defaultProps = {
  level: 2,
  steps: []
}

export {
  FormStepper
}
