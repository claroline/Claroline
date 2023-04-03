import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text'
import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MENU_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentMeta} from '#/main/app/content/components/meta'
import {Form} from '#/main/app/content/form/components/form' // TODO : use container instead
import {FormFieldset} from '#/main/app/content/form/components/fieldset'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {constants} from '#/main/app/content/form/constants'
import {createFormDefinition} from '#/main/app/content/form/utils'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'

function getSectionId(section, formId = null) {
  let id = formId ? `${formId}-` : ''

  id += section.id || toKey(section.title)

  return id
}

function getSectionErrors(sectionFields = [], errors = {}) {
  let sectionErrors = []

  sectionFields.map(field => {
    if (get(errors, field.name)) {
      sectionErrors.push(get(errors, field.name))
    }

    if (field.linked) {
      sectionErrors = sectionErrors.concat(getSectionErrors(field.linked, errors))
    }
  })

  return sectionErrors
}

const FormModes = props =>
  <div className="form-mode">
    <span className="hidden-xs">{trans('form_mode')}</span>

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
  const hLevel = props.level + (props.title ? 1 : 0)
  let hDisplay
  if (props.displayLevel) {
    hDisplay = props.displayLevel + (props.title ? 1 : 0)
  }

  const sections = createFormDefinition(props.mode, props.definition || props.sections, props.locked, props.data)

  const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
  const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
  const openedSection = otherSections.find(section => section.defaultOpened)

  const disabled = typeof props.disabled === 'function' ? props.disabled(props.data) : props.disabled

  return (
    <Form
      id={props.id}
      className={props.className}
      embedded={props.embedded}
      disabled={disabled}
      level={props.level}
      displayLevel={props.displayLevel}
      title={props.title}
      errors={!isEmpty(props.errors)}
      validating={props.validating}
      pendingChanges={props.pendingChanges}
      alertExit={props.alertExit}
      save={props.save}
      cancel={props.cancel}
      lock={props.lock}
      getLock={props.getLock}
      unlock={props.unlock}
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

      {primarySections.map(primarySection =>
        <div
          id={`${getSectionId(primarySection, props.id)}-section`}
          key={primarySection.id || toKey(primarySection.title)}
          className={classes('form-primary-section panel panel-default', primarySection.className)}
        >
          <ContentTitle
            level={hLevel}
            displayed={false}
            title={primarySection.title}
            subtitle={primarySection.subtitle}
          />

          <div className="panel-body">
            {!isEmpty(primarySection.actions) &&
              <Toolbar
                id={`${getSectionId(primarySection, props.id)}-actions`}
                buttonName="btn"
                className="text-right form-group"
                size="sm"
                actions={primarySection.actions}
              />
            }

            <FormFieldset
              id={getSectionId(primarySection, props.id)}
              mode={props.mode}
              disabled={props.disabled || primarySection.disabled}
              fields={primarySection.fields}
              data={props.data}
              errors={props.errors}
              help={primarySection.help}
              validating={props.validating}
              updateProp={props.updateProp}
              setErrors={props.setErrors}
            >
              {primarySection.component && createElement(primarySection.component)}
              {!primarySection.component && primarySection.render && primarySection.render(props.data, props.errors)}
            </FormFieldset>
          </div>
        </div>
      )}

      {0 !== otherSections.length &&
        <FormSections
          level={hLevel}
          displayLevel={hDisplay}
          defaultOpened={openedSection ? getSectionId(openedSection, props.id) : undefined}
        >
          {otherSections.map(section => (
            <FormSection
              id={getSectionId(section, props.id)}
              className={section.className}
              key={getSectionId(section, props.id)}
              icon={section.icon}
              title={section.title}
              subtitle={section.subtitle}
              errors={getSectionErrors(section.fields, props.errors)}
              validating={props.validating}
              actions={section.actions}
            >
              <FormFieldset
                id={`${getSectionId(section, props.id)}-fieldset`}
                fill={true}
                className="panel-body"
                mode={props.mode}
                disabled={props.disabled || (typeof section.disabled === 'function' ? section.disabled(props.data) : section.disabled)}
                fields={section.fields}
                data={props.data}
                errors={props.errors}
                help={section.help}
                validating={props.validating}
                updateProp={props.updateProp}
                setErrors={props.setErrors}
              >
                {section.component && createElement(section.component)}
                {!section.component && section.render && section.render(props.data, props.errors)}
              </FormFieldset>
            </FormSection>
          ))}
        </FormSections>
      }

      {props.children}
    </Form>
  )
}

FormData.propTypes = {
  id: T.string.isRequired,
  /**
   * Is the form embed into another ?
   *
   * Permits to know if we use a <form> or a <fieldset> tag.
   */
  embedded: T.bool,
  level: T.number,
  displayLevel: T.number,
  title: T.string,
  className: T.string,
  mode: T.string.isRequired,
  disabled: T.oneOfType([T.bool, T.func]),
  errors: T.object,
  validating: T.bool,
  pendingChanges: T.bool,
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

  getLock: T.func,
  unlock: T.func,

  /**
   * The save action of the form.
   */
  save: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
    // todo find a way to document custom action type props
  }),

  /**
   * The cancel action of the form (if provided.
   */
  cancel: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
    // todo find a way to document custom action type props
  }),
  setMode: T.func.isRequired,
  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired,
  children: T.node
}

FormData.defaultProps = {
  level: 2,
  disabled: false,
  mode: constants.FORM_MODE_DEFAULT,
  data: {}
}

export {
  FormData
}
