import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {Button} from '#/main/app/action'
import {trans} from '#/main/app/intl/translation'
import {Redirect as RedirectTypes} from '#/main/app/router/prop-types'
import {Routes, NavLink} from '#/main/app/router'

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
          done: props.activeIndex > stepIndex
        })}
      >
        <span className="form-step-badge">{stepIndex+1}</span>
        {step.title}
      </NavLink>
    )}
  </nav>

FormStepperNav.propTypes = {
  path: T.string,
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
        href={`#${props.path}${props.nextStep}`}
      >
        {trans('form_next_step')}
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

      {props.submit.label || trans('save')}
    </button>
  </div>

FormStepperFooter.propTypes = {
  path: T.string,
  nextStep: T.string,
  //find a much better definition
  action: T.shape(ButtonTypes.propTypes),
  submit: T.shape({
    icon: T.string,
    label: T.string,
    action: T.oneOfType([T.string, T.func]).isRequired
  }).isRequired
}

const FormStepper = props => {
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

      <Routes
        path={props.path}
        routes={props.steps}
        redirect={props.redirect}
      />

      <FormStepperFooter
        path={props.path}
        action={props.steps[activeIndex].action}
        nextStep={props.steps[activeIndex+1] ? props.steps[activeIndex+1].path : undefined}
        submit={props.submit}
      />
    </div>
  )
}

FormStepper.propTypes = {
  path: T.string,
  className: T.string,
  location: T.shape({
    pathname: T.string
  }),
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

export {
  FormStepper
}
