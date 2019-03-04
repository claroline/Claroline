import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import set from 'lodash/set'

import {toKey} from '#/main/core/scaffolding/text/utils'

import {Heading} from '#/main/core/layout/components/heading'
import {ContentMeta} from '#/main/app/content/meta/components/meta'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {SubSet} from '#/main/core/layout/form/components/fieldset/sub-set'

import {createFormDefinition} from '#/main/app/content/form/utils'
import {DataFormSection as DataFormSectionTypes} from '#/main/app/content/form/prop-types'
import {Form} from '#/main/app/content/form/components/form'
import {FormGroup} from '#/main/app/content/form/components/group'

import {DataInput} from '#/main/app/data/components/input'

// todo : restore readOnly
// todo : add auto focus

class FormField extends Component {
  constructor(props) {
    super(props)

    this.onChange = this.onChange.bind(this)
    this.onError = this.onError.bind(this)
  }

  onChange(value) {
    this.props.update(this.props.name, value, this.props.onChange)
  }

  onError(error) {
    this.props.setErrors(this.props.name, error)
  }

  render() {
    return (
      <DataInput
        id={this.props.name.replace(/\./g, '-')}
        type={this.props.type}
        label={this.props.label}
        hideLabel={this.props.hideLabel}
        options={this.props.options}
        help={this.props.help}
        placeholder={this.props.placeholder}

        required={this.props.required}
        disabled={this.props.disabled}
        validating={this.props.validating}

        value={this.props.value}
        error={this.props.error}

        onChange={this.onChange}
        onError={this.onError}
      />
    )
  }
}

FormField.propTypes = {
  name: T.string.isRequired,
  type: T.string.isRequired,
  label: T.string.isRequired,
  hideLabel: T.bool,
  options: T.object, // depends on the data type
  help: T.oneOfType([T.string, T.arrayOf(T.string)]),
  placeholder: T.any, // depends on the data type
  size: T.oneOf(['sm', 'lg']),
  onChange: T.func,

  // field state
  required: T.bool,
  disabled: T.bool,
  validating: T.bool,

  // field data
  value: T.any, // depends on the data type
  error: T.oneOfType([
    T.string,
    T.arrayOf(T.string),
    T.object
  ]),

  // form methods
  update: T.func.isRequired,
  setErrors: T.func.isRequired
}

class FormData extends Component {
  constructor(props) {
    super(props)

    this.update = this.update.bind(this)
    this.setErrors = this.setErrors.bind(this)
  }

  renderFields(fields) {
    let rendered = []

    fields.map(field => {
      if (field.render) {
        rendered.push(
          <FormGroup
            key={field.name}
            id={field.name.replace(/\./g, '-')}
            label={field.label}
            hideLabel={field.hideLabel}
            help={field.help}
            optional={!field.required}
            error={get(this.props.errors, field.name)}
            warnOnly={!this.props.validating}
          >
            {field.render(this.props.data)}
          </FormGroup>
        )
      } else {
        rendered.push(
          <FormField
            key={field.name}
            name={field.name}
            type={field.type}
            label={field.label}
            hideLabel={field.hideLabel}
            options={field.options}
            help={field.help}
            placeholder={field.placeholder}

            required={field.required}
            disabled={this.props.disabled || (typeof field.disabled === 'function' ? field.disabled(this.props.data) : field.disabled)}
            validating={this.props.validating}

            value={field.calculated ? field.calculated(this.props.data) : get(this.props.data, field.name)}
            error={get(this.props.errors, field.name)}
            onChange={field.onChange}
            update={this.update}
            setErrors={this.setErrors}
          />
        )
      }

      if (field.linked && 0 !== field.linked.length) {
        rendered.push(
          <SubSet key={`${field.name}-subset`}>
            {this.renderFields(field.linked)}
          </SubSet>
        )
      }
    })

    return rendered
  }

  update(name, value, onChange) {
    this.props.updateProp(name, value)
    if (onChange) {
      onChange(value)
    }
  }

  setErrors(name, error) {
    const newErrors = this.props.errors ? cloneDeep(this.props.errors) : {}
    set(newErrors, name, error)

    this.props.setErrors(newErrors)
  }

  render() {
    const hLevel = this.props.level + (this.props.title ? 1 : 0)
    let hDisplay
    if (this.props.displayLevel) {
      hDisplay = this.props.displayLevel + (this.props.title ? 1 : 0)
    }

    const sections = createFormDefinition(this.props.sections, this.props.data)

    const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
    const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
    const openedSection = otherSections.find(section => section.defaultOpened)

    return (
      <Form
        id={this.props.id}
        className={this.props.className}
        embedded={this.props.embedded}
        disabled={this.props.disabled}
        level={this.props.level}
        displayLevel={this.props.displayLevel}
        title={this.props.title}
        errors={!isEmpty(this.props.errors)}
        validating={this.props.validating}
        pendingChanges={this.props.pendingChanges}
        save={this.props.save}
        cancel={this.props.cancel}
        lock={this.props.lock}
        getLock={this.props.getLock}
        setLock={this.props.setLock}
        unlock={this.props.unlock}
      >
        {this.props.meta &&
          <ContentMeta
            creator={get(this.props.data, 'meta.creator')}
            created={get(this.props.data, 'meta.created')}
            updated={get(this.props.data, 'meta.updated')}
          />
        }

        {primarySections.map(primarySection =>
          <div
            id={primarySection.id || toKey(primarySection.title)}
            key={primarySection.id || toKey(primarySection.title)}
            className="form-primary-section panel panel-default"
          >
            <fieldset className="panel-body">
              <Heading level={hLevel} displayed={false}>
                {primarySection.title}
              </Heading>

              {this.renderFields(primarySection.fields)}
            </fieldset>
          </div>
        )}

        {0 !== otherSections.length &&
          <FormSections
            level={hLevel}
            displayLevel={hDisplay}
            defaultOpened={openedSection ? (openedSection.id || toKey(openedSection.title)) : undefined}
          >
            {otherSections.map(section =>
              <FormSection
                id={section.id || toKey(section.title)}
                key={section.id || toKey(section.title)}
                icon={section.icon}
                title={section.title}
                subtitle={section.subtitle}
                errors={this.props.errors}
                validating={this.props.validating}
              >
                {this.renderFields(section.fields)}
              </FormSection>
            )}
          </FormSections>
        }

        {this.props.children}
      </Form>
    )
  }
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
  title: T.string,
  className: T.string,
  disabled: T.bool,
  errors: T.object,
  validating: T.bool,
  pendingChanges: T.bool,

  meta: T.bool,
  data: T.object,
  sections: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )).isRequired,

  lock: T.shape({
    id: T.string.isRequired,
    className: T.string.isRequired
  }),

  getLock: T.func.isRequired,
  setLock: T.func.isRequired,
  unlock: T.func.isRequired,

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
  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired,
  children: T.node
}

FormData.defaultProps = {
  level: 2,
  data: {}
}

export {
  FormData
}
