import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Form} from '#/main/app/content/form/containers/form'

import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {FormContent} from '#/main/app/content/form/containers/content'

const FormData = (props) => {
  const disabled = typeof props.disabled === 'function' ? props.disabled(props.data) : props.disabled

  return (
    <Form
      id={props.id}
      name={props.name}
      dataPart={props.dataPart}
      className={props.className}
      flush={props.flush}
      disabled={disabled}
      level={props.level}
      displayLevel={props.displayLevel}
      title={props.title}
      alertExit={props.alertExit}
      save={props.save}
      cancel={props.cancel}
      buttons={props.buttons}
      target={props.target}
      onSave={props.onSave}
    >
      <FormContent
        id={props.id}
        name={props.name}
        dataPart={props.dataPart}
        level={props.level}
        displayLevel={props.displayLevel}
        flush={props.flush}
        autoFocus={props.autoFocus}
        disabled={disabled}
        definition={props.definition || props.sections}
        size={props.size}
        locked={props.locked}
      />

      {props.children}
    </Form>
  )
}

FormData.propTypes = {
  id: T.string,

  /**
   * The name of the data in the form.
   *
   * It should be the key in the store where the list has been mounted
   * (aka where `makeFormReducer()` has been called).
   */
  name: T.string.isRequired,

  /**
   * Permits to connect the form on a sub-part of the data.
   * This is useful when the form is broken in multiple steps/pages
   *
   * It MUST be a valid lodash/get selector.
   */
  dataPart: T.string,

  /**
   * Do we need to show the form buttons ?
   */
  buttons: T.bool,

  /**
   * The API target of the Form (only used if props.buttons === true).
   *
   * NB. It can be a route definition or a function to calculate the final route.
   * If a function is provided it's called with the current data & new flag as param.
   */
  target: T.oneOfType([T.string, T.array, T.func]),
  level: T.number,
  displayLevel: T.number,
  size: T.string,
  flush: T.bool,
  autoFocus: T.bool,
  title: T.string,
  className: T.string,
  disabled: T.oneOfType([T.bool, T.func]),
  /**
   * Alerts the user when leaving the form with unsaved changes
   */
  alertExit: T.bool,

  data: T.object,
  /**
   * @deprecated use definition instead
   */
  sections: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )),
  definition: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )).isRequired,
  locked: T.arrayOf(T.string), // a list of inputs to be locked in form

  onSave: T.func,

  /**
   * The save action of the form.
   */
  save: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
  }),

  /**
   * The cancel action of the form if provided.
   */
  cancel: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
  }),
  children: T.node
}

FormData.defaultProps = {
  level: 2,
  disabled: false,
  flush: false,
  data: {}
}

export {
  FormData
}
