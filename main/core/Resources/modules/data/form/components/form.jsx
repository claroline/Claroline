import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'
import {toKey} from '#/main/core/scaffolding/text/utils'

import {Heading} from '#/main/core/layout/components/heading'
import {ContentMeta} from '#/main/app/content/meta/components/meta'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'
import {SubSet} from '#/main/core/layout/form/components/fieldset/sub-set'
import {ToggleableSet} from '#/main/core/layout/form/components/fieldset/toggleable-set'

import {createFormDefinition} from '#/main/core/data/form/utils'
import {DataFormSection as DataFormSectionTypes} from '#/main/core/data/form/prop-types'
import {FormField} from '#/main/core/data/form/components/field'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'
import {FormActions} from '#/main/core/data/form/components/actions'

const AdvancedSection = props =>
  <ToggleableSet
    showText={props.showText}
    hideText={props.hideText}
  >
    {props.fields.map(field =>
      <FormField
        key={field.name}
        {...field}
      />
    )}
  </ToggleableSet>

AdvancedSection.propTypes = {
  showText: T.string,
  hideText: T.string,
  fields: T.array.isRequired
}

AdvancedSection.defaultProps = {
  showText: trans('show_advanced_options'),
  hideText: trans('hide_advanced_options')
}

const FormWrapper = props => props.embedded ?
  <fieldset className={classes('form data-form', props.className)}>
    {props.children}
  </fieldset>
  :
  <form action="#" className={classes('form data-form', props.className)}>
    {props.children}
  </form>

FormWrapper.propTypes = {
  className: T.string,
  embedded: T.bool,
  children: T.node.isRequired
}

FormWrapper.defaultProps = {
  embedded: false
}

class Form extends Component {
  constructor(props) {
    super(props)

    this.warnPendingChanges = this.warnPendingChanges.bind(this)
  }

  warnPendingChanges(e) {
    if (this.props.pendingChanges) {
      // note: this is supposed to be the text displayed in the browser built-in
      // popup (see https://developer.mozilla.org/en-US/docs/Web/API/WindowEventHandlers/onbeforeunload#Example)
      // but it doesn't seem to be actually used in modern browsers. We use it
      // here because a string is needed anyway.
      e.returnValue = trans('unsaved_changes_warning')

      return e.returnValue
    }
  }

  componentDidMount() {
    window.addEventListener('beforeunload', this.warnPendingChanges)
  }

  componentWillUnmount() {
    // todo warn also here
    // if client route has changed, it will not trigger before unload
    window.removeEventListener('beforeunload', this.warnPendingChanges)
  }

  renderFields(fields) {
    let rendered = []

    fields.map(field => {
      if (field.render) {
        rendered.push(
          <FormGroup
            id={field.name}
            key={field.name}
            label={field.label}
            hideLabel={field.hideLabel}
            help={field.help}
          >
            {field.render(this.props.data)}
          </FormGroup>
        )
      } else {
        rendered.push(
          <FormField
            {...field}
            key={field.name}
            value={field.calculated ? field.calculated(this.props.data) : get(this.props.data, field.name)}
            disabled={this.props.disabled || (typeof field.disabled === 'function' ? field.disabled(this.props.data) : field.disabled)}
            validating={this.props.validating}
            error={get(this.props.errors, field.name)}
            updateProp={this.props.updateProp}
            setErrors={this.props.setErrors}
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
      <FormWrapper embedded={this.props.embedded} className={this.props.className}>
        {this.props.title &&
          <Heading level={this.props.level} displayLevel={this.props.displayLevel}>
            {this.props.title}
          </Heading>
        }

        {this.props.meta &&
          <ContentMeta meta={get(this.props.data, 'meta')} />
        }

        {primarySections.map(primarySection =>
          <div key={toKey(primarySection.title)} className="form-primary-section panel panel-default">
            <fieldset className="panel-body">
              <Heading level={hLevel} displayed={false}>
                {primarySection.title}
              </Heading>

              {this.renderFields(primarySection.fields)}

              {primarySection.advanced &&
                <AdvancedSection {...primarySection.advanced} />
              }
            </fieldset>
          </div>
        )}

        {0 !== otherSections.length &&
          <FormSections
            level={hLevel}
            displayLevel={hDisplay}
            defaultOpened={openedSection ? openedSection.id : undefined}
          >
            {otherSections.map(section =>
              <FormSection
                key={toKey(section.title)}
                icon={section.icon}
                title={section.title}
                errors={this.props.errors}
                validating={this.props.validating}
              >
                {this.renderFields(section.fields)}

                {section.advanced &&
                  <AdvancedSection {...section.advanced} />
                }
              </FormSection>
            )}
          </FormSections>
        }

        {this.props.children}

        {(this.props.save || this.props.cancel) &&
          <FormActions
            save={this.props.save ? merge({}, this.props.save, {
              disabled: this.props.disabled || this.props.save.disabled || !(this.props.pendingChanges && (!this.props.validating || isEmpty(this.props.errors)))
            }) : undefined}
            cancel={this.props.cancel}
          />
        }
      </FormWrapper>
    )
  }
}

Form.propTypes = {
  /**
   * Is the form embed into another ?
   *
   * Permits to know if we use a <form> or a <fieldset> tag.
   */
  embedded: T.bool,
  level: T.number,
  displayLevel: T.number,
  title: T.string,
  data: T.object,
  errors: T.object,
  validating: T.bool,
  pendingChanges: T.bool,
  sections: T.arrayOf(T.shape(
    DataFormSectionTypes.propTypes
  )).isRequired,
  setErrors: T.func.isRequired,
  updateProp: T.func.isRequired,
  className: T.string,
  disabled: T.bool,
  children: T.node,
  meta: T.bool,

  /**
   * The save action of the form (if provided, form toolbar will be displayed).
   */
  save: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
    // todo find a way to document custom action type props
  }),

  /**
   * The cancel action of the form (if provided, form toolbar will be displayed).
   */
  cancel: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
    // todo find a way to document custom action type props
  })
}

Form.defaultProps = {
  disabled: false,
  data: {},
  level: 2,
  errors: {},
  validating: false,
  pendingChanges: false,
  meta: false
}

export {
  Form
}
