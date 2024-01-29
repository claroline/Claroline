import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentMeta} from '#/main/app/content/components/meta'
import {Form} from '#/main/app/content/form/containers/form'

import {constants} from '#/main/app/content/form/constants'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {FormContent} from '#/main/app/content/form/containers/content'

const FormModes = props =>
  <div className="form-mode">
    <span className="d-none d-sm-block">{trans('form_mode')}</span>

    <Button
      id="data-form-mode-menu"
      className="btn btn-link"
      type={MENU_BUTTON}
      label={constants.FORM_MODES[props.current]}
      primary={true}
      menu={{
        label: trans('form_modes'),
        align: 'right',
        items: Object.keys(constants.FORM_MODES).map(mode => ({
          type: CALLBACK_BUTTON,
          label: constants.FORM_MODES[mode],
          active: props.current === mode,
          callback: () => props.updateMode(mode)
        }))
      }}
    />
  </div>

FormModes.propTypes = {
  current: T.string.isRequired,
  updateMode: T.func.isRequired
}

const FormData = (props) => {
  const disabled = typeof props.disabled === 'function' ? props.disabled(props.data) : props.disabled

  return (
    <Form
      id={props.id}
      name={props.name}
      dataPart={props.dataPart}
      className={props.className}
      embedded={props.embedded}
      flush={props.flush}
      disabled={disabled}
      level={props.level}
      displayLevel={props.displayLevel}
      title={props.title}
      alertExit={props.alertExit}
      lock={props.lock}
      save={props.save}
      cancel={props.cancel}
      buttons={props.buttons}
      target={props.target}
    >
      {props.meta &&
        <ContentMeta
          creator={get(props.data, 'meta.creator')}
          created={get(props.data, 'meta.created')}
          updated={get(props.data, 'meta.updated')}
        />
      }

      {false &&
        <FormModes
          current={props.mode}
          updateMode={props.setMode}
        />
      }

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
        locked={props.locked}
      />

      {props.children}
    </Form>
  )
}

FormData.propTypes = {
  id: T.string,
  /**
   * Is the form embed into another ?
   *
   * Permits to know if we use a <form> or a <fieldset> tag.
   */
  embedded: T.bool,
  level: T.number,
  displayLevel: T.number,
  flush: T.bool,
  autoFocus: T.bool,
  title: T.string,
  className: T.string,
  mode: T.string.isRequired,
  disabled: T.oneOfType([T.bool, T.func]),
  /**
   * Alerts the user when leaving the form with unsaved changes
   */
  alertExit: T.bool,

  meta: T.bool,
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

  lock: T.shape({
    id: T.string.isRequired,
    className: T.string.isRequired
  }),

  /**
   * The save action of the form.
   */
  save: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
    // todo find a way to document custom action type props
  }),

  /**
   * The cancel action of the form if provided.
   */
  cancel: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
    // todo find a way to document custom action type props
  }),
  setMode: T.func.isRequired,
  children: T.node
}

FormData.defaultProps = {
  level: 2,
  disabled: false,
  flush: false,
  autoFocus: true,
  mode: constants.FORM_MODE_DEFAULT,
  data: {}
}

export {
  FormData
}
