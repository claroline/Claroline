import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {makeId} from '#/main/core/scaffolding/id'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {FormSection, FormSections} from '#/main/app/content/form/components/sections'

import {FormData} from '#/main/app/content/form/containers/data'

const SectionParameters = props =>
  <FormSection
    {...omit(props, ['name', 'dataPart', 'index', 'fields', 'remove'])}
    icon={props.icon ? `fa fa-fw fa-${props.icon}` : undefined}
    title={props.title || trans('facet_section')}
    subtitle={props.description}
    className="embedded-form-section"
    actions={[
      {
        name: 'delete',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash',
        label: trans('delete', {}, 'actions'),
        callback: props.remove,
        dangerous: true,
        confirm: {
          title: trans('facet_remove_section'),
          message: trans('facet_remove_section_question')
        }
      }
    ]}
  >
    <FormData
      embedded={true}
      level={3}
      name={props.name}
      dataPart={`${props.dataPart}[${props.index}]`}
      definition={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'title',
              type: 'string',
              label: trans('title')
            }, {
              name: 'meta.description',
              type: 'string',
              label: trans('description')
            }, {
              name: 'fields',
              type: 'fields',
              label: trans('fields_list'),
              required: true,
              options: {
                placeholder: trans('facet_section_no_field'),
                fields: props.fields,
                min: 1
              }
            }
          ]
        }, {
          id: 'help',
          icon: 'fa fa-fw fa-circle-question',
          title: trans('help'),
          fields: [
            {
              name: 'help',
              type: 'html',
              label: trans('message')
            }
          ]
        }, {
          id: 'display',
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'display.icon',
              type: 'icon',
              label: trans('icon')
            }, {
              name: 'display.order',
              type: 'number',
              label: trans('order')
            }
          ]
        }
      ]}
    />
  </FormSection>

SectionParameters.propTypes = {
  index: T.number.isRequired,
  name: T.string.isRequired,
  dataPart: T.string.isRequired,
  icon: T.string,
  title: T.string,
  description: T.string,
  fields: T.array,
  remove: T.func.isRequired
}
const FormParameters = (props) =>
  <Fragment>
    {0 < props.sections.length &&
      <FormSections level={2}>
        {props.sections.map((section, sectionIndex) =>
          <SectionParameters
            id={section.id}
            key={section.id}
            index={sectionIndex}
            name={props.name}
            dataPart={props.dataPart}
            icon={get(section, 'display.icon')}
            title={section.title}
            description={get(section, 'meta.description')}
            fields={props.fields}
            remove={() => {
              const updatedSections = merge([], props.sections)

              const pos = updatedSections.findIndex(current => current.id === section.id)
              if (-1 !== pos) {
                updatedSections.splice(pos, 1)
              }

              // reorder sections
              updatedSections.map((section, sectionIndex) => {
                section.position = sectionIndex

                return section
              })

              props.update(props.name, props.dataPart, updatedSections)
            }}
          />
        )}
      </FormSections>
    }

    {0 === props.sections.length &&
      <ContentPlaceholder
        size="lg"
        title={trans('facet_no_section')}
        help={trans('facet_no_section_help')}
      />
    }

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-block btn-emphasis component-container"
      label={trans('facet_section_add')}
      callback={() => props.update(props.name, props.dataPart, [].concat(props.sections, [{
        id: makeId(),
        title: '',
        position: props.sections.length,
        fields: []
      }]))}
      primary={true}
    />
  </Fragment>

FormParameters.propTypes = {
  name: T.string.isRequired,
  dataPart: T.string.isRequired,
  sections: T.arrayOf(T.shape({

  })),
  fields: T.arrayOf(T.shape({

  })),
  update: T.func.isRequired
}

FormParameters.defaultProps = {
  sections: []
}

export {
  FormParameters
}
