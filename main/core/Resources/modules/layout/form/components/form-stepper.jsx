import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {Button} from '#/main/app/action'
import {t} from '#/main/core/translation'
import {Redirect as RedirectTypes} from '#/main/app/router/prop-types'
import {Router, Routes, NavLink, withRouter} from '#/main/app/router'

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
        to={step.path}
        exact={step.exact}
        className={classes('form-stepper-link', {
          done: props.activeIndex > stepIndex
        })}
      >
        <span className="form-step-badge">{stepIndex+1}</span>
        {step.title}
      </NavLink>
    )}
  </nav>

FormStepperNav.propTypes = {
  activeIndex: T.number.isRequired,
  steps: T.arrayOf(T.shape({
    title: T.string.isRequired,
    // route part
    path: T.string.isRequired,
    component: T.any.isRequired, // todo find better typing
    exact: T.bool,
    onEnter: T.func,
    onLeave: T.func
  })).isRequired
}

/**
 * Renders the form footer (aka. next and submit buttons).
 *
 * @param props
 * @constructor
 */
const FormStepperFooter = props =>
  <div className="form-stepper-footer">
    {props.nextStep && !props.action &&
      <a
        className="btn btn-next btn-link"
        href={`#${props.nextStep}`}
      >
        {t('form_next_step')}
        <span className="fa fa-angle-double-right" />
      </a>
    }

    {props.action &&
      <Button className="btn btn-next btn-link"
        {...props.action}
      />
    }

    <button
      className="btn btn-submit btn-primary"
      onClick={props.submit.action}
    >
      {props.submit.icon &&
        <span className={props.submit.icon} />
      }

      {props.submit.label || t('save')}
    </button>
  </div>

FormStepperFooter.propTypes = {
  nextStep: T.string,
  //find a much better definition
  action: T.shape(ButtonTypes.propTypes),
  submit: T.shape({
    icon: T.string,
    label: T.string,
    action: T.oneOfType([T.string, T.func]).isRequired
  }).isRequired
}

const FormStepperComponent = withRouter(props => {
  let activeIndex = props.steps.findIndex(step => props.location && step.path === props.location.pathname)
  if (-1 === activeIndex) {
    activeIndex = 0
  }

  return (
    <div className={classes('form-stepper', props.className)}>
      <FormStepperNav
        steps={props.steps}
        activeIndex={activeIndex}
      />

      <Routes
        routes={props.steps}
        redirect={props.redirect}
        blockingSteps={props.blockingSteps}
      />

      <FormStepperFooter
        action={props.steps[activeIndex].action}
        nextStep={props.steps[activeIndex+1] ? props.steps[activeIndex+1].path : undefined}
        submit={props.submit}
      />
    </div>
  )
})

FormStepperComponent.propTypes = {
  className: T.string,
  steps: T.arrayOf(T.shape({
    title: T.string.isRequired,
    // route part
    path: T.string.isRequired,
    component: T.any.isRequired, // todo find better typing
    exact: T.bool,
    onEnter: T.func,
    onLeave: T.func,
    action:  T.shape(ButtonTypes.propTypes)
  })).isRequired,
  redirect: T.arrayOf(T.shape(
    RedirectTypes.propTypes
  )),
  submit: T.shape({
    icon: T.string,
    label: T.string,
    action: T.oneOfType([T.string, T.func]).isRequired
  }).isRequired
}

const FormStepper = props =>
  <Router>
    <FormStepperComponent {...props} />
  </Router>

FormStepper.propTypes = {
  className: T.string,
  steps: T.arrayOf(T.shape({
    title: T.string.isRequired,
    // route part
    path: T.string.isRequired,
    component: T.any.isRequired, // todo find better typing
    exact: T.bool,
    onEnter: T.func,
    onLeave: T.func,
    action:  T.shape(ButtonTypes.propTypes)
  })).isRequired,
  redirect: T.arrayOf(T.shape(
    RedirectTypes.propTypes
  )),
  submit: T.shape({
    icon: T.string,
    label: T.string,
    action: T.oneOfType([T.string, T.func]).isRequired
  }).isRequired
}

FormStepper.defaultProps = {
  blockingSteps: false
}

export {
  FormStepper
}
