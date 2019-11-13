import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {Route as RouteTypes, Redirect as RedirectTypes} from '#/main/app/router/prop-types'
import {withRouter, Routes, NavLink} from '#/main/app/router'

/**
 * Renders the form navigation.
 *
 * @param props
 * @constructor
 */
const FormStepperNav = props =>
  <nav className="form-stepper-nav">
    {props.steps.map((step, stepIndex) =>
      <NavLink
        key={stepIndex}
        to={props.path+step.path}
        exact={step.exact}
        className={classes('form-stepper-link', {
          active: props.activeIndex === stepIndex,
          done: props.activeIndex > stepIndex
        })}
      >
        <span className="form-step-badge">
          {step.icon &&
            <span className={step.icon} />
          }

          {!step.icon && (stepIndex+1)}
        </span>
        {step.title}
      </NavLink>
    )}
  </nav>

FormStepperNav.propTypes = {
  path: T.string,
  activeIndex: T.number.isRequired,
  steps: T.arrayOf(T.shape(
    merge({
      icon: T.string,
      title: T.string.isRequired
    }, RouteTypes.propTypes))
  ).isRequired
}

/**
 * Renders the form footer (aka. next and submit buttons).
 *
 * @param props
 * @constructor
 */
const FormStepperFooter = props =>
  <div className="form-stepper-footer">
    {props.previousStep &&
      <Button
        className="btn btn-link btn-emphasis"
        type={LINK_BUTTON}
        label={trans('previous')}
        target={props.path+props.previousStep}
      />
    }

    {props.nextStep &&
      <Button
        className="btn btn-emphasis btn-next"
        type={LINK_BUTTON}
        label={trans('next')}
        target={props.path+props.nextStep}
        primary={true}
      />
    }

    {!props.nextStep &&
      <Button
        className="btn btn-emphasis btn-next"
        {...props.submit}
        primary={true}
      />
    }
  </div>

FormStepperFooter.propTypes = {
  path: T.string,
  previousStep: T.string,
  nextStep: T.string,
  submit: T.shape(
    ActionTypes.propTypes
  ).isRequired
}

const FormStepperComponent = props => {
  let activeIndex = props.steps.findIndex(step => props.location && props.path+step.path === props.location.pathname)
  if (-1 === activeIndex) {
    activeIndex = 0
  }

  return (
    <div className={classes('form-stepper', props.className)}>
      <FormStepperNav
        path={props.path}
        steps={props.steps}
        activeIndex={activeIndex}
      />

      <ContentTitle
        level={props.level}
        displayLevel={props.displayLevel}
        title={props.steps[activeIndex].title}
      />

      <Routes
        path={props.path}
        routes={props.steps}
        redirect={props.redirect}
      />

      <FormStepperFooter
        path={props.path}
        previousStep={props.steps[activeIndex-1] ? props.steps[activeIndex-1].path : undefined}
        nextStep={props.steps[activeIndex+1] ? props.steps[activeIndex+1].path : undefined}
        submit={props.submit}
      />
    </div>
  )
}

const FormStepper = withRouter(FormStepperComponent)

FormStepper.propTypes = {
  path: T.string,
  className: T.string,
  level: T.number,
  displayLevel: T.number,
  location: T.shape({
    pathname: T.string
  }),
  steps: T.arrayOf(T.shape(
    merge({
      icon: T.string,
      title: T.string.isRequired
    }, RouteTypes.propTypes))
  ).isRequired,
  redirect: T.arrayOf(T.shape(
    RedirectTypes.propTypes
  )),
  submit: T.shape(
    ActionTypes.propTypes
  ).isRequired
}

FormStepper.defaultProps = {
  path: '',
  level: 2
}

export {
  FormStepper
}
